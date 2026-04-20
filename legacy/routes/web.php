<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\Entity\HomeEntityController;

Route::get('/', function () {
    return view('welcome');
});

// UI Kit Demo (development only)
Route::get('/ui-kit', function () {
    return view('ui-kit');
})->name('ui-kit');

// Rutas para el flujo de ajuste de uso
Route::middleware(['auth', \App\Http\Middleware\CheckPlanEntities::class])->group(function () {
    Route::get('/usage-adjustments', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'index'])->name('usage_adjustments.index');
    Route::get('/usage-adjustments/{invoice}/edit', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'edit'])->name('usage_adjustments.edit');
    Route::post('/usage-adjustments/{invoice}', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'update'])->name('usage_adjustments.update');
    Route::post('/usage-adjustments/{invoice}/unlock', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'unlock'])->name('usage_adjustments.unlock');
    Route::get('/usage-adjustments/{invoice}', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'show'])->name('usage_adjustments.show');

    // Ruta para el panel de consumo
    Route::get('/consumption/panel', [\App\Http\Controllers\Consumption\PanelController::class, 'index'])->name('consumption.panel');
    Route::get('/consumption/cards', [\App\Http\Controllers\Consumption\PanelController::class, 'cards'])->name('consumption.cards');
    Route::get('/consumption/panel/{invoice}', [\App\Http\Controllers\Consumption\PanelController::class, 'show'])->name('consumption.panel.show');
});

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
// Registro deshabilitado - Solo por invitación en producción
// Route::get('register', [\App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
// Route::post('register', [\App\Http\Controllers\Auth\RegisterController::class, 'register']);

Route::middleware(['auth', \App\Http\Middleware\CheckPlanEntities::class])->group(function () {
    // Rutas CRUD para contratos (Unificadas en Livewire)
    Route::get('/contracts', \App\Livewire\Physical\ContractManager::class)->name('contracts.index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rutas para equipos (Equipment) - globales
    Route::get('/equipment/portable', [\App\Http\Controllers\Physical\EquipmentController::class, 'createPortable'])->name('equipment.create_portable');
    Route::resource('equipment', \App\Http\Controllers\Physical\EquipmentController::class);
    Route::resource('efficiency-benchmarks', \App\Http\Controllers\Admin\EfficiencyBenchmarkController::class);

    // Rutas para Reemplazos - refinamiento (sin entidad)

    // Ruta para log de mantenimiento (por equipo)
    Route::get('/equipment/{equipment}/maintenance', \App\Livewire\Recommendations\MaintenanceManager::class)->name('equipment.maintenance');

    // =====================================================
    // RUTAS POR TIPO DE ENTIDAD
    // =====================================================

    // --- Hogares (Home) ---
    Route::prefix('entities/home')->name('entities.home.')->group(function () {
        // CRUD principal
        Route::get('/', [HomeEntityController::class, 'index'])->name('index');
        Route::get('/create', [HomeEntityController::class, 'create'])->name('create');
        Route::post('/', [HomeEntityController::class, 'store'])->name('store');
        Route::get('/{entity}', [HomeEntityController::class, 'show'])->name('show');
        Route::get('/{entity}/edit', [HomeEntityController::class, 'edit'])->name('edit');
        Route::put('/{entity}', [HomeEntityController::class, 'update'])->name('update');
        Route::delete('/{entity}', [HomeEntityController::class, 'destroy'])->name('destroy');

        // Recomendaciones
        Route::get('/{entity}/budget', \App\Livewire\Recommendations\BudgetAnalysis::class)->name('budget');
        Route::get('/{entity}/solar-panels', \App\Livewire\Recommendations\SolarPanels::class)->name('solar_panels');
        Route::get('/{entity}/solar-water-heater', \App\Livewire\Recommendations\SolarWaterHeater::class)->name('solar_water_heater');
        Route::get('/{entity}/standby-analysis', \App\Livewire\Recommendations\StandbyAnalysis::class)->name('standby_analysis');
        Route::get('/{entity}/grid-optimization', \App\Livewire\Recommendations\GridOptimization::class)->name('grid_optimization');
        Route::get('/{entity}/smart-meter-demo', [\App\Http\Controllers\Consumption\SmartMeterController::class, 'demo'])->name('smart_meter_demo');
        Route::get('/{entity}/replacements', \App\Livewire\Recommendations\ReplacementManager::class)->name('replacements');
        Route::get('/{entity}/maintenance', \App\Livewire\Recommendations\MaintenanceManager::class)->name('maintenance');

        // Vacaciones
        Route::get('/{entity}/vacation', \App\Livewire\Recommendations\VacationPlan::class)->name('vacation');

        // Salud Térmica
        Route::get('/{entity}/thermal', [\App\Http\Controllers\Recommendations\ThermalComfortController::class, 'index'])->name('thermal');
        Route::get('/{entity}/thermal/wizard', \App\Livewire\Recommendations\ThermalWizard::class)->name('thermal.wizard');
        Route::get('/{entity}/thermal/result', [\App\Http\Controllers\Recommendations\ThermalComfortController::class, 'result'])->name('thermal.result');

        // Medidor/Contrato
        Route::get('/{entity}/meter', \App\Livewire\Physical\ContractManager::class)->name('meter');

        // Facturas
        Route::get('/{entity}/invoices', \App\Livewire\Physical\InvoiceManager::class)->name('invoices');

        // Infraestructura (Ambientes y Equipos)
        Route::get('/{entity}/infrastructure', \App\Livewire\Physical\InfrastructureManager::class)->name('rooms');

        // Ajustes de Uso (contextual a la entidad)
        Route::get('/{entity}/usage-adjustments', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'indexForEntity'])->name('usage_adjustments');
        Route::get('/{entity}/usage-adjustments/{invoice}/edit', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'editForEntity'])->name('usage_adjustments.edit');
        Route::post('/{entity}/usage-adjustments/{invoice}', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'updateForEntity'])->name('usage_adjustments.update');
        Route::post('/{entity}/usage-adjustments/{invoice}/unlock', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'unlockForEntity'])->name('usage_adjustments.unlock');
        Route::get('/{entity}/usage-adjustments/{invoice}', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'showForEntity'])->name('usage_adjustments.show');

        // Panel de Consumo (contextual a la entidad)
        Route::get('/{entity}/consumption', [\App\Http\Controllers\Consumption\PanelController::class, 'showForEntity'])->name('consumption');
        Route::get('/{entity}/consumption/{invoice}', [\App\Http\Controllers\Consumption\PanelController::class, 'show'])->name('consumption.show');
    });

    // --- Oficinas (Office) ---
    Route::prefix('entities/office')->name('entities.office.')->group(function () {
        // CRUD principal
        Route::get('/', [\App\Http\Controllers\Entity\OfficeEntityController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Entity\OfficeEntityController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Entity\OfficeEntityController::class, 'store'])->name('store');
        Route::get('/{entity}', [\App\Http\Controllers\Entity\OfficeEntityController::class, 'show'])->name('show');
        Route::get('/{entity}/edit', [\App\Http\Controllers\Entity\OfficeEntityController::class, 'edit'])->name('edit');
        Route::put('/{entity}', [\App\Http\Controllers\Entity\OfficeEntityController::class, 'update'])->name('update');
        Route::delete('/{entity}', [\App\Http\Controllers\Entity\OfficeEntityController::class, 'destroy'])->name('destroy');

        // Recomendaciones
        Route::get('/{entity}/budget', \App\Livewire\Recommendations\BudgetAnalysis::class)->name('budget');
        Route::get('/{entity}/solar-panels', \App\Livewire\Recommendations\SolarPanels::class)->name('solar_panels');
        Route::get('/{entity}/solar-water-heater', \App\Livewire\Recommendations\SolarWaterHeater::class)->name('solar_water_heater');
        Route::get('/{entity}/standby-analysis', \App\Livewire\Recommendations\StandbyAnalysis::class)->name('standby_analysis');
        Route::get('/{entity}/grid-optimization', \App\Livewire\Recommendations\GridOptimization::class)->name('grid_optimization');
        Route::get('/{entity}/smart-meter-demo', [\App\Http\Controllers\Consumption\SmartMeterController::class, 'demo'])->name('smart_meter_demo');
        Route::get('/{entity}/replacements', \App\Livewire\Recommendations\ReplacementManager::class)->name('replacements');
        Route::get('/{entity}/maintenance', \App\Livewire\Recommendations\MaintenanceManager::class)->name('maintenance');

        // Salud Térmica
        Route::get('/{entity}/thermal', [\App\Http\Controllers\Recommendations\ThermalComfortController::class, 'index'])->name('thermal');
        Route::get('/{entity}/thermal/wizard', \App\Livewire\Recommendations\ThermalWizard::class)->name('thermal.wizard');
        Route::get('/{entity}/thermal/result', [\App\Http\Controllers\Recommendations\ThermalComfortController::class, 'result'])->name('thermal.result');

        // Medidor/Contrato
        Route::get('/{entity}/meter', \App\Livewire\Physical\ContractManager::class)->name('meter');

        // Facturas
        Route::get('/{entity}/invoices', \App\Livewire\Physical\InvoiceManager::class)->name('invoices');

        // Infraestructura (Ambientes y Equipos)
        Route::get('/{entity}/infrastructure', \App\Livewire\Physical\InfrastructureManager::class)->name('rooms');

        // Ajustes de Uso (contextual a la entidad)
        Route::get('/{entity}/usage-adjustments', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'indexForEntity'])->name('usage_adjustments');
        Route::get('/{entity}/usage-adjustments/{invoice}/edit', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'editForEntity'])->name('usage_adjustments.edit');
        Route::post('/{entity}/usage-adjustments/{invoice}', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'updateForEntity'])->name('usage_adjustments.update');
        Route::post('/{entity}/usage-adjustments/{invoice}/unlock', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'unlockForEntity'])->name('usage_adjustments.unlock');
        Route::get('/{entity}/usage-adjustments/{invoice}', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'showForEntity'])->name('usage_adjustments.show');

        // Panel de Consumo (contextual a la entidad)
        Route::get('/{entity}/consumption', [\App\Http\Controllers\Consumption\PanelController::class, 'showForEntity'])->name('consumption');
        Route::get('/{entity}/consumption/{invoice}', [\App\Http\Controllers\Consumption\PanelController::class, 'show'])->name('consumption.show');
    });

    // --- Comercios (Trade) ---
    Route::prefix('entities/trade')->name('entities.trade.')->group(function () {
        // CRUD principal
        Route::get('/', [\App\Http\Controllers\Entity\TradeEntityController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Entity\TradeEntityController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Entity\TradeEntityController::class, 'store'])->name('store');
        Route::get('/{entity}', [\App\Http\Controllers\Entity\TradeEntityController::class, 'show'])->name('show');
        Route::get('/{entity}/edit', [\App\Http\Controllers\Entity\TradeEntityController::class, 'edit'])->name('edit');
        Route::put('/{entity}', [\App\Http\Controllers\Entity\TradeEntityController::class, 'update'])->name('update');
        Route::delete('/{entity}', [\App\Http\Controllers\Entity\TradeEntityController::class, 'destroy'])->name('destroy');

        // Recomendaciones
        Route::get('/{entity}/budget', \App\Livewire\Recommendations\BudgetAnalysis::class)->name('budget');
        Route::get('/{entity}/solar-panels', \App\Livewire\Recommendations\SolarPanels::class)->name('solar_panels');
        Route::get('/{entity}/solar-water-heater', \App\Livewire\Recommendations\SolarWaterHeater::class)->name('solar_water_heater');
        Route::get('/{entity}/standby-analysis', \App\Livewire\Recommendations\StandbyAnalysis::class)->name('standby_analysis');
        Route::get('/{entity}/grid-optimization', \App\Livewire\Recommendations\GridOptimization::class)->name('grid_optimization');
        Route::get('/{entity}/smart-meter-demo', [\App\Http\Controllers\Consumption\SmartMeterController::class, 'demo'])->name('smart_meter_demo');
        Route::get('/{entity}/replacements', \App\Livewire\Recommendations\ReplacementManager::class)->name('replacements');
        Route::get('/{entity}/maintenance', \App\Livewire\Recommendations\MaintenanceManager::class)->name('maintenance');

        // Salud Térmica
        Route::get('/{entity}/thermal', [\App\Http\Controllers\Recommendations\ThermalComfortController::class, 'index'])->name('thermal');
        Route::get('/{entity}/thermal/wizard', \App\Livewire\Recommendations\ThermalWizard::class)->name('thermal.wizard');
        Route::get('/{entity}/thermal/result', [\App\Http\Controllers\Recommendations\ThermalComfortController::class, 'result'])->name('thermal.result');

        // Medidor/Contrato
        Route::get('/{entity}/meter', \App\Livewire\Physical\ContractManager::class)->name('meter');

        // Facturas
        Route::get('/{entity}/invoices', \App\Livewire\Physical\InvoiceManager::class)->name('invoices');

        // Infraestructura (Ambientes y Equipos)
        Route::get('/{entity}/infrastructure', \App\Livewire\Physical\InfrastructureManager::class)->name('rooms');

        // Ajustes de Uso (contextual a la entidad)
        Route::get('/{entity}/usage-adjustments', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'indexForEntity'])->name('usage_adjustments');
        Route::get('/{entity}/usage-adjustments/{invoice}/edit', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'editForEntity'])->name('usage_adjustments.edit');
        Route::post('/{entity}/usage-adjustments/{invoice}', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'updateForEntity'])->name('usage_adjustments.update');
        Route::post('/{entity}/usage-adjustments/{invoice}/unlock', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'unlockForEntity'])->name('usage_adjustments.unlock');
        Route::get('/{entity}/usage-adjustments/{invoice}', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'showForEntity'])->name('usage_adjustments.show');

        // Panel de Consumo (contextual a la entidad)
        Route::get('/{entity}/consumption', [\App\Http\Controllers\Consumption\PanelController::class, 'showForEntity'])->name('consumption');
        Route::get('/{entity}/consumption/{invoice}', [\App\Http\Controllers\Consumption\PanelController::class, 'show'])->name('consumption.show');
    });

    // =====================================================
    // RUTAS LEGACY (mantener compatibilidad temporal)
    // =====================================================
    Route::get('/entities', [\App\Http\Controllers\DashboardController::class, 'index'])->name('entities.index');
    Route::get('/entities/create', [\App\Http\Controllers\Entity\HomeEntityController::class, 'create'])->name('entities.create');
    Route::post('/entities', [\App\Http\Controllers\Entity\HomeEntityController::class, 'store'])->name('entities.store');
    Route::get('/entities/{entity}', [\App\Http\Controllers\Entity\HomeEntityController::class, 'show'])->name('entities.show');
    Route::get('/entities/{entity}/edit', [\App\Http\Controllers\Entity\HomeEntityController::class, 'edit'])->name('entities.edit');
    Route::put('/entities/{entity}', [\App\Http\Controllers\Entity\HomeEntityController::class, 'update'])->name('entities.update');
    Route::delete('/entities/{entity}', [\App\Http\Controllers\Entity\HomeEntityController::class, 'destroy'])->name('entities.destroy');

    // Legacy routes anidadas (para compatibilidad mientras se migra)
    Route::get('/entities/{entity}/budget', \App\Livewire\Recommendations\BudgetAnalysis::class)->name('entities.budget');
    Route::get('/entities/{entity}/grid-optimization', \App\Livewire\Recommendations\GridOptimization::class)->name('grid.optimization');
    Route::get('/entities/{entity}/smart-meter-demo', [\App\Http\Controllers\Consumption\SmartMeterController::class, 'demo'])->name('smart_meter.demo');
    Route::get('/entities/{entity}/infrastructure', \App\Livewire\Physical\InfrastructureManager::class)->name('rooms.index');
    Route::get('/entities/{entity}/meter', \App\Livewire\Physical\ContractManager::class)->name('entities.meter.index');
    Route::get('/entities/{entity}/invoices', \App\Livewire\Physical\InvoiceManager::class)->name('entities.invoices.index');
    Route::get('/entities/{entity}/maintenance', \App\Livewire\Recommendations\MaintenanceManager::class)->name('maintenance.index');
    Route::get('/entities/{entity}/replacements', \App\Livewire\Recommendations\ReplacementManager::class)->name('replacements.index');
    Route::get('/entities/{entity}/thermal', [\App\Http\Controllers\Recommendations\ThermalComfortController::class, 'index'])->name('thermal.index');
    Route::get('/entities/{entity}/thermal/wizard', \App\Livewire\Recommendations\ThermalWizard::class)->name('thermal.wizard');
    Route::get('/entities/{entity}/thermal/result', [\App\Http\Controllers\Recommendations\ThermalComfortController::class, 'result'])->name('thermal.result');
});

// =====================================================
// SUPER ADMIN ROUTES
// =====================================================
Route::middleware(['auth', 'super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/audit/dashboard', [\App\Http\Controllers\AuditDashboardController::class, 'index'])->name('audit.dashboard');
});

// API Locations
Route::middleware(['auth'])->group(function () {
    Route::get('/api/provinces/{province}/localities', [\App\Http\Controllers\Api\LocationController::class, 'getLocalitiesByProvince'])->name('api.localities');
});
