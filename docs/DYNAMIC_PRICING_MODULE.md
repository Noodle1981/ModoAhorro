# DYNAMIC_PRICING_MODULE.md
# Especificación: Integración con Mercado Libre para Precios Dinámicos

## 1. Objetivo
Mantener actualizados los precios de los equipos eficientes (`average_market_price`) utilizando la API de Mercado Libre, para que el cálculo de ROI sea siempre preciso sin mantenimiento manual.

---

## 2. Actualización de Base de Datos

**Tabla:** `efficiency_benchmarks`
Agregar columnas para gestionar la búsqueda automatizada.

```php
$table->string('meli_search_term')->nullable()->comment('Ej: Aire Acondicionado Inverter 3500 frigorias');
$table->string('meli_category_id')->nullable()->comment('ID de categoría en ML para filtrar mejor');
$table->string('affiliate_link')->nullable()->comment('Link monetizado al listado');
$table->timestamp('price_last_updated_at')->nullable();


Servicio de Actualización de Precios (MarketPriceService)
Este servicio se ejecuta vía Laravel Scheduler (Cron) semanalmente.

Lógica del Algoritmo:

PHP

public function updateBenchmarkPrices()
{
    $benchmarks = EfficiencyBenchmark::whereNotNull('meli_search_term')->get();

    foreach ($benchmarks as $bench) {
        // 1. Consultar API Pública de Mercado Libre (No requiere Token para búsquedas GET)
        // Endpoint: [https://api.mercadolibre.com/sites/MLA/search?q=](https://api.mercadolibre.com/sites/MLA/search?q=)...
        $results = Http::get("[https://api.mercadolibre.com/sites/MLA/search](https://api.mercadolibre.com/sites/MLA/search)", [
            'q' => $bench->meli_search_term,
            'category' => $bench->meli_category_id, // Opcional
            'condition' => 'new',
            'limit' => 15 // Traemos 15 items
        ])->json()['results'];

        if (empty($results)) continue;

        // 2. Sanitización de Precios (Estadística Robusta)
        $prices = collect($results)->pluck('price')->sort()->values();

        // Eliminamos el top 2 mas barato (a veces son repuestos o errores)
        // Eliminamos el top 2 mas caro (precios absurdos)
        $cleanPrices = $prices->slice(2, -2);

        if ($cleanPrices->isEmpty()) $avgPrice = $prices->avg();
        else $avgPrice = $cleanPrices->avg();

        // 3. Guardar en Base de Datos
        $bench->update([
            'average_market_price' => $avgPrice,
            'price_last_updated_at' => now()
        ]);
        
        // Opcional: Guardar el link del mejor producto para el botón "Comprar"
        // $bench->affiliate_link = $results[0]['permalink'];
    }
}
4. Visualización en el Dashboard
En la tarjeta de recomendación, mostrar la "frescura" del dato para generar confianza.

Aire Acondicionado Inverter

Inversión Estimada: $850,000

(Precio promedio de mercado actualizado hace 2 días)

5. Monetización (Afiliados)
Importante: Mercado Libre tiene programa de afiliados.

En la tabla efficiency_benchmarks, el campo affiliate_link no debe llevar al producto específico (que puede pausarse), sino a la Búsqueda Filtrada.

Generar links tipo: https://listado.mercadolibre.com.ar/aire-inverter#AGEN_ID=TU_ID_AFILIADO.

Flujo:

Usuario ve "ROI en 24 meses".

Clic en [ Ver Precios Reales en ML ].

Tu sistema redirige con tu link de afiliado.

El usuario compra.

Tú ganas comisión.


### ¿Por qué NO usar la API de Mercado Pago?

* **Mercado Pago (MP):** Es para procesar cobros (si tú vendieras los aires acondicionados directamente).
* **Mercado Libre (ML):** Es para buscar productos y precios. Esta es la que necesitas.

### Resumen del Flujo

1.  **Tu Base de Datos:** Tiene la inteligencia ("Un aire inverter ahorra 35%").
2.  **API ML (Background):** Le pone el precio ("Ese aire hoy cuesta $850k").
3.  **Frontend:** Muestra la oportunidad financiera perfecta.
4.  **Clic:** Te genera ingresos pasivos.