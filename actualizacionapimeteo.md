# Propuesta de Refactorización: Carga Asíncrona de la API Climática

## El Problema Actual
Actualmente, la obtención de datos climáticos se realiza de forma **síncrona** dentro de `AnalysisController.php` al cargar la vista de Sintonía Fina (`UsageAdjustmentDetail.vue`). Si la base de datos local no tiene el registro histórico para las fechas solicitadas, el controlador hace una petición HTTP a OpenMeteo.
Esto provoca que el hilo de ejecución de PHP se bloquee hasta recibir respuesta, lo que el usuario percibe como un "congelamiento" de la pantalla al hacer clic en "Configurar Uso" antes de que la página logre navegar.

## La Solución: API Frontend + Backend Desacoplado
Mover la responsabilidad de la carga climática al frontend mediante una petición AJAX (Inertia/Axios) una vez que la pantalla de Sintonía Fina ya ha cargado. 

### Pasos de Implementación Futura

#### 1. Crear un Nuevo Controlador para la API Climática
Crear `app/Http/Controllers/Api/ClimateController.php`.
```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ClimateService;
use App\Models\Entity;
use Illuminate\Http\Request;

class ClimateController extends Controller
{
    public function fetch(Request $request)
    {
        $request->validate([
            'entity_id' => 'required|exists:entities,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $entity = Entity::find($request->entity_id);
        $climateService = app(ClimateService::class);
        
        // Esto bajará de la BD o consultará a OpenMeteo
        $data = $climateService->loadDataForDateRange($entity, $request->start_date, $request->end_date);
        
        return response()->json($data);
    }
}
```

#### 2. Registrar la Ruta de la API
En `routes/web.php` o `routes/api.php`:
```php
Route::get('/api/climate/fetch', [\App\Http\Controllers\Api\ClimateController::class, 'fetch'])->name('api.climate.fetch');
```

#### 3. Limpiar `AnalysisController.php`
Remover la llamada a `ClimateService` de `AnalysisController@usageAdjustmentDetail`. Enviar por defecto los datos climáticos en `0` a la vista de Vue para que inicie la renderización instantáneamente.

#### 4. Actualizar `UsageAdjustmentDetail.vue`
En el componente Vue, al montarse (`onMounted`), hacer la petición para obtener los datos climáticos.
Mientras carga, mostrar un *Badge* o estado visual de *"Cargando datos climáticos..."*.

```vue
<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const isLoadingClimate = ref(true);
const climateData = ref({ cooling_days: 0, heating_days: 0 });

onMounted(async () => {
    try {
        const response = await axios.get(route('api.climate.fetch'), {
            params: {
                entity_id: props.entity.id,
                start_date: props.period.start,
                end_date: props.period.end
            }
        });
        
        climateData.value = response.data;
        
        // Actualizar el objeto props o inyectar a calculateKwh
        props.period.cooling_days = climateData.value.cooling_days;
        props.period.heating_days = climateData.value.heating_days;
        
    } catch (error) {
        console.error("Error cargando clima:", error);
    } finally {
        isLoadingClimate.value = false;
    }
});
</script>
```

#### 5. Mejoras de UI (Spinner/Skeleton)
- Deshabilitar el botón de "Guardar" mientras `isLoadingClimate` sea `true`.
- Mostrar un icono de recarga girando al lado del badge *"Calculado"* en los equipos de Climatización hasta que llegue la data.
