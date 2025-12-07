# Próximos Pasos - Sprint 1

## 🎯 Objetivo del Sprint 1
**Validación y Trazabilidad de Equipos**

Evitar desviaciones absurdas entre consumo calculado y facturado, y rastrear equipos en el tiempo.

---

## 📋 Tareas del Sprint 1

### 1. Crear `ValidationService`

**Archivo**: `app/Services/Core/ValidationService.php`

```php
<?php

namespace App\Services\Core;

use App\Models\Invoice;

class ValidationService
{
    /**
     * Calcula la desviación entre consumo calculado y facturado
     */
    public function calculateDeviation(Invoice $invoice, float $calculatedConsumption): array
    {
        $billed = $invoice->total_energy_consumed_kwh ?? 0;
        $deviation = abs($calculatedConsumption - $billed);
        $deviationPercent = $billed > 0 ? ($deviation / $billed) * 100 : 0;
        
        return [
            'billed' => $billed,
            'calculated' => $calculatedConsumption,
            'deviation' => $deviation,
            'deviation_percent' => round($deviationPercent, 2),
            'alert_level' => $this->getAlertLevel($deviationPercent),
        ];
    }
    
    /**
     * Determina el nivel de alerta según la desviación
     */
    private function getAlertLevel(float $deviationPercent): string
    {
        return match(true) {
            $deviationPercent < 10 => 'success',  // ✅ Excelente
            $deviationPercent < 30 => 'warning',  // ⚠️ Revisar
            default => 'danger'                    // ❌ Crítico
        };
    }
    
    /**
     * Genera sugerencias de ajuste
     */
    public function getSuggestions(Invoice $invoice, float $calculatedConsumption): array
    {
        $suggestions = [];
        $diff = $invoice->total_energy_consumed_kwh - $calculatedConsumption;
        
        if ($diff > 50) {
            $suggestions[] = 'Revisa equipos de climatización (mayor impacto)';
            $suggestions[] = 'Verifica equipos de alto consumo (>1000W)';
            $suggestions[] = '¿Olvidaste ajustar algún equipo?';
        }
        
        return $suggestions;
    }
}
```

---

### 2. Migración: Agregar Fechas a Equipos

**Comando**:
```bash
php artisan make:migration add_installation_dates_to_equipment
```

**Archivo**: `database/migrations/YYYY_MM_DD_HHMMSS_add_installation_dates_to_equipment.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->date('installed_at')->nullable()->after('is_active');
            $table->date('removed_at')->nullable()->after('installed_at');
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn(['installed_at', 'removed_at']);
        });
    }
};
```

---

### 3. Migración: Bloqueo de Períodos

**Comando**:
```bash
php artisan make:migration add_usage_locked_to_invoices
```

**Archivo**: `database/migrations/YYYY_MM_DD_HHMMSS_add_usage_locked_to_invoices.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('usage_locked')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('usage_locked');
        });
    }
};
```

---

### 4. Actualizar `ConsumptionPanelController`

**Archivo**: `app/Http/Controllers/ConsumptionPanelController.php`

```php
use App\Services\Core\ValidationService;

public function index()
{
    $invoice = Invoice::with(['contract', 'equipmentUsages.equipment'])->find(1);
    $consumptionService = new ConsumptionAnalysisService();
    $validationService = new ValidationService();
    
    $consumos = $consumptionService->calculateInvoiceConsumption($invoice);
    $totalEnergia = array_sum($consumos);
    
    // Validación
    $validation = $validationService->calculateDeviation($invoice, $totalEnergia);
    $suggestions = $validationService->getSuggestions($invoice, $totalEnergia);
    
    // ... resto del código ...
    
    return view('consumption.panel', [
        'invoice' => $invoice,
        'totalEnergia' => $totalEnergia,
        'consumos' => $consumos,
        'consumoPorCategoria' => $consumoPorCategoria,
        'validation' => $validation,
        'suggestions' => $suggestions,
    ]);
}
```

---

### 5. Actualizar Vista del Panel

**Archivo**: `resources/views/consumption/panel.blade.php`

Agregar después de la sección de comparación:

```blade
{{-- Alerta de Desviación --}}
@if($validation['alert_level'] === 'danger')
    <div class="alert alert-danger mt-3">
        <h5>⚠️ Desviación Alta Detectada</h5>
        <p>El consumo calculado difiere en <strong>{{ $validation['deviation_percent'] }}%</strong> del facturado.</p>
        
        @if(count($suggestions) > 0)
            <p class="mb-2"><strong>Sugerencias:</strong></p>
            <ul class="mb-3">
                @foreach($suggestions as $suggestion)
                    <li>{{ $suggestion }}</li>
                @endforeach
            </ul>
        @endif
        
        <a href="{{ route('usage-adjustments.edit', $invoice->id) }}" class="btn btn-warning">
            Revisar Ajustes
        </a>
    </div>
@elseif($validation['alert_level'] === 'warning')
    <div class="alert alert-warning mt-3">
        <strong>⚠️ Desviación moderada:</strong> {{ $validation['deviation_percent'] }}%
        <a href="{{ route('usage-adjustments.edit', $invoice->id) }}" class="btn-link">
            Revisar
        </a>
    </div>
@endif
```

---

### 6. Filtrar Equipos por Período

**Archivo**: `app/Http/Controllers/UsageAdjustmentController.php`

Actualizar método `edit`:

```php
public function edit($invoiceId)
{
    $invoice = Invoice::findOrFail($invoiceId);
    
    // Verificar si está bloqueado
    if ($invoice->usage_locked) {
        return redirect()->back()->with('warning', 
            '⚠️ Este período está cerrado. ¿Deseas reabrirlo?'
        );
    }
    
    $entity = $invoice->contract->entity;
    
    // Filtrar equipos que existían en el período
    $rooms = $entity->rooms()->with(['equipment' => function($q) use ($invoice) {
        $q->where(function($query) use ($invoice) {
            // Equipo instalado antes o durante el período
            $query->where(function($q) use ($invoice) {
                $q->whereNull('installed_at')
                  ->orWhere('installed_at', '<=', $invoice->end_date);
            })
            // Y no removido antes del período
            ->where(function($q) use ($invoice) {
                $q->whereNull('removed_at')
                  ->orWhere('removed_at', '>=', $invoice->start_date);
            });
        });
    }])->get();
    
    $usages = $invoice->equipmentUsages()->get()->keyBy('equipment_id');
    
    return view('usage_adjustments.edit', compact('invoice', 'rooms', 'usages'));
}
```

---

## ✅ Checklist de Completitud

- [ ] `ValidationService` creado y testeado
- [ ] Migración de fechas aplicada
- [ ] Migración de bloqueo aplicada
- [ ] `ConsumptionPanelController` actualizado
- [ ] Vista del panel actualizada con alertas
- [ ] `UsageAdjustmentController` filtra por período
- [ ] Testing manual completo
- [ ] Commit realizado
- [ ] README actualizado

---

## 🧪 Plan de Testing

### Test 1: Validación de Desviación
1. Crear factura con 624 kWh facturado
2. Ajustar equipos para ~300 kWh calculado
3. Verificar alerta roja aparece
4. Verificar sugerencias se muestran

### Test 2: Equipos por Período
1. Crear equipo con `installed_at` = 2025-06-01
2. Editar factura de período 2025-01-15 a 2025-03-20
3. Verificar equipo NO aparece
4. Editar factura de período 2025-06-01 a 2025-08-30
5. Verificar equipo SÍ aparece

### Test 3: Bloqueo de Período
1. Marcar factura como `usage_locked = true`
2. Intentar editar ajustes
3. Verificar mensaje de advertencia

---

## 📊 Métricas de Éxito

- ✅ Desviación <10%: Badge verde
- ⚠️ Desviación 10-30%: Badge amarillo
- ❌ Desviación >30%: Alerta roja con sugerencias
- ✅ Equipos históricos correctos (no aparecen si no existían)

---

## 🚀 Después del Sprint 1

Una vez completado, proceder a **Sprint 2: Asistencia Climática**
