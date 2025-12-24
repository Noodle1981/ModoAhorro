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
Route::get('register', [\App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [\App\Http\Controllers\Auth\RegisterController::class, 'register']);

Route::middleware(['auth', \App\Http\Middleware\CheckPlanEntities::class])->group(function () {
    // Rutas CRUD para contratos
    Route::get('/contracts', [\App\Http\Controllers\Physical\ContractController::class, 'index'])->name('contracts.index');
    Route::get('/contracts/create', [\App\Http\Controllers\Physical\ContractController::class, 'create'])->name('contracts.create');
    Route::post('/contracts', [\App\Http\Controllers\Physical\ContractController::class, 'store'])->name('contracts.store');
    Route::get('/contracts/{contract}/edit', [\App\Http\Controllers\Physical\ContractController::class, 'edit'])->name('contracts.edit');
    Route::put('/contracts/{contract}', [\App\Http\Controllers\Physical\ContractController::class, 'update'])->name('contracts.update');
    Route::delete('/contracts/{contract}', [\App\Http\Controllers\Physical\ContractController::class, 'destroy'])->name('contracts.destroy');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rutas para equipos (Equipment) - globales
    Route::get('/equipment/portable', [\App\Http\Controllers\Physical\EquipmentController::class, 'createPortable'])->name('equipment.create_portable');
    Route::resource('equipment', \App\Http\Controllers\Physical\EquipmentController::class);
    Route::resource('efficiency-benchmarks', \App\Http\Controllers\Admin\EfficiencyBenchmarkController::class);

    // Rutas para Reemplazos - refinamiento (sin entidad)
    Route::get('/replacements/{equipment}/refine', [\App\Http\Controllers\Recommendations\ReplacementController::class, 'refine'])->name('replacements.refine');
    Route::put('/replacements/{equipment}/refine', [\App\Http\Controllers\Recommendations\ReplacementController::class, 'updateRefinement'])->name('replacements.update_refinement');

    // Ruta para log de mantenimiento (sin entidad)
    Route::post('/equipment/{equipment}/maintenance-log', [\App\Http\Controllers\Recommendations\MaintenanceController::class, 'storeLog'])->name('maintenance.log.store');

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
        Route::get('/{entity}/budget', [\App\Http\Controllers\Recommendations\BudgetController::class, 'index'])->name('budget');
        Route::get('/{entity}/solar-water-heater', [\App\Http\Controllers\Recommendations\SolarController::class, 'waterHeater'])->name('solar_water_heater');
        Route::get('/{entity}/standby-analysis', [\App\Http\Controllers\Recommendations\StandbyController::class, 'analysis'])->name('standby_analysis');
        Route::patch('/{entity}/standby-analysis/{equipment}/toggle', [\App\Http\Controllers\Recommendations\StandbyController::class, 'toggle'])->name('standby.toggle');
        Route::get('/{entity}/grid-optimization', [\App\Http\Controllers\Recommendations\GridController::class, 'optimization'])->name('grid_optimization');
        Route::get('/{entity}/smart-meter-demo', [\App\Http\Controllers\Consumption\SmartMeterController::class, 'demo'])->name('smart_meter_demo');
        Route::get('/{entity}/replacements', [\App\Http\Controllers\Recommendations\ReplacementController::class, 'index'])->name('replacements');
        Route::get('/{entity}/maintenance', [\App\Http\Controllers\Recommendations\MaintenanceController::class, 'index'])->name('maintenance');

        // Vacaciones
        Route::get('/{entity}/vacation', [\App\Http\Controllers\Recommendations\VacationController::class, 'index'])->name('vacation');
        Route::post('/{entity}/vacation', [\App\Http\Controllers\Recommendations\VacationController::class, 'calculate'])->name('vacation.calculate');
        Route::post('/{entity}/vacation/confirm', [\App\Http\Controllers\Recommendations\VacationController::class, 'confirm'])->name('vacation.confirm');

        // Salud Térmica
        Route::get('/{entity}/thermal', [\App\Http\Controllers\Recommendations\ThermalComfortController::class, 'index'])->name('thermal');
        Route::get('/{entity}/thermal/wizard', [\App\Http\Controllers\Recommendations\ThermalComfortController::class, 'wizard'])->name('thermal.wizard');
        Route::post('/{entity}/thermal', [\App\Http\Controllers\Recommendations\ThermalComfortController::class, 'store'])->name('thermal.store');
        Route::get('/{entity}/thermal/result', [\App\Http\Controllers\Recommendations\ThermalComfortController::class, 'result'])->name('thermal.result');

        // Medidor/Contrato
        Route::get('/{entity}/meter', [\App\Http\Controllers\Physical\ContractController::class, 'showForEntity'])->name('meter');

        // Facturas
        Route::get('/{entity}/invoices', [\App\Http\Controllers\Physical\InvoiceController::class, 'index'])->name('invoices');
        Route::get('/{entity}/invoices/create', [\App\Http\Controllers\Physical\InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/{entity}/invoices', [\App\Http\Controllers\Physical\InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/{entity}/invoices/{invoice}/edit', [\App\Http\Controllers\Physical\InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('/{entity}/invoices/{invoice}', [\App\Http\Controllers\Physical\InvoiceController::class, 'update'])->name('invoices.update');

        // Habitaciones
        Route::get('/{entity}/rooms', [\App\Http\Controllers\Physical\RoomController::class, 'index'])->name('rooms');
        Route::get('/{entity}/rooms/create', [\App\Http\Controllers\Physical\RoomController::class, 'create'])->name('rooms.create');
        Route::post('/{entity}/rooms', [\App\Http\Controllers\Physical\RoomController::class, 'store'])->name('rooms.store');
        Route::get('/{entity}/rooms/{room}', [\App\Http\Controllers\Physical\RoomController::class, 'show'])->name('rooms.show');
        Route::get('/{entity}/rooms/{room}/edit', [\App\Http\Controllers\Physical\RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/{entity}/rooms/{room}', [\App\Http\Controllers\Physical\RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/{entity}/rooms/{room}', [\App\Http\Controllers\Physical\RoomController::class, 'destroy'])->name('rooms.destroy');

        // Equipos en habitaciones
        Route::get('/{entity}/rooms/{room}/equipment', [\App\Http\Controllers\Physical\RoomEquipmentController::class, 'dashboard'])->name('rooms.equipment');
        Route::post('/{entity}/rooms/{room}/equipment', [\App\Http\Controllers\Physical\RoomEquipmentController::class, 'store'])->name('rooms.equipment.store');
        Route::get('/{entity}/rooms/{room}/equipment/{equipment}/edit', [\App\Http\Controllers\Physical\RoomEquipmentController::class, 'edit'])->name('rooms.equipment.edit');
        Route::put('/{entity}/rooms/{room}/equipment/{equipment}', [\App\Http\Controllers\Physical\RoomEquipmentController::class, 'update'])->name('rooms.equipment.update');
        Route::delete('/{entity}/rooms/{room}/equipment/{equipment}', [\App\Http\Controllers\Physical\RoomEquipmentController::class, 'destroy'])->name('rooms.equipment.destroy');

        // Ajustes de Uso (contextual a la entidad)
        Route::get('/{entity}/usage-adjustments', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'indexForEntity'])->name('usage_adjustments');
        Route::get('/{entity}/usage-adjustments/{invoice}/edit', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'edit'])->name('usage_adjustments.edit');
        Route::post('/{entity}/usage-adjustments/{invoice}', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'update'])->name('usage_adjustments.update');
        Route::post('/{entity}/usage-adjustments/{invoice}/unlock', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'unlock'])->name('usage_adjustments.unlock');
        Route::get('/{entity}/usage-adjustments/{invoice}', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'show'])->name('usage_adjustments.show');

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
        Route::get('/{entity}/budget', [\App\Http\Controllers\Recommendations\BudgetController::class, 'index'])->name('budget');
        Route::get('/{entity}/solar-water-heater', [\App\Http\Controllers\Recommendations\SolarController::class, 'waterHeater'])->name('solar_water_heater');
        Route::get('/{entity}/standby-analysis', [\App\Http\Controllers\Recommendations\StandbyController::class, 'analysis'])->name('standby_analysis');
        Route::patch('/{entity}/standby-analysis/{equipment}/toggle', [\App\Http\Controllers\Recommendations\StandbyController::class, 'toggle'])->name('standby.toggle');
        Route::get('/{entity}/grid-optimization', [\App\Http\Controllers\Recommendations\GridController::class, 'optimization'])->name('grid_optimization');
        Route::get('/{entity}/smart-meter-demo', [\App\Http\Controllers\Consumption\SmartMeterController::class, 'demo'])->name('smart_meter_demo');
        Route::get('/{entity}/replacements', [\App\Http\Controllers\Recommendations\ReplacementController::class, 'index'])->name('replacements');
        Route::get('/{entity}/maintenance', [\App\Http\Controllers\Recommendations\MaintenanceController::class, 'index'])->name('maintenance');

        // Salud Térmica
        Route::get('/{entity}/thermal', [\App\Http\Controllers\Recommendations\ThermalComfortController::class, 'index'])->name('thermal');
        Route::get('/{entity}/thermal/wizard', [\App\Http\Controllers\Recommendations\ThermalComfortController::class, 'wizard'])->name('thermal.wizard');
        Route::post('/{entity}/thermal', [\App\Http\Controllers\Recommendations\ThermalComfortController::class, 'store'])->name('thermal.store');
        Route::get('/{entity}/thermal/result', [\App\Http\Controllers\Recommendations\ThermalComfortController::class, 'result'])->name('thermal.result');

        // Medidor/Contrato
        Route::get('/{entity}/meter', [\App\Http\Controllers\Physical\ContractController::class, 'showForEntity'])->name('meter');

        // Facturas
        Route::get('/{entity}/invoices', [\App\Http\Controllers\Physical\InvoiceController::class, 'index'])->name('invoices');
        Route::get('/{entity}/invoices/create', [\App\Http\Controllers\Physical\InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/{entity}/invoices', [\App\Http\Controllers\Physical\InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/{entity}/invoices/{invoice}/edit', [\App\Http\Controllers\Physical\InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('/{entity}/invoices/{invoice}', [\App\Http\Controllers\Physical\InvoiceController::class, 'update'])->name('invoices.update');

        // Habitaciones
        Route::get('/{entity}/rooms', [\App\Http\Controllers\Physical\RoomController::class, 'index'])->name('rooms');
        Route::get('/{entity}/rooms/create', [\App\Http\Controllers\Physical\RoomController::class, 'create'])->name('rooms.create');
        Route::post('/{entity}/rooms', [\App\Http\Controllers\Physical\RoomController::class, 'store'])->name('rooms.store');
        Route::get('/{entity}/rooms/{room}', [\App\Http\Controllers\Physical\RoomController::class, 'show'])->name('rooms.show');
        Route::get('/{entity}/rooms/{room}/edit', [\App\Http\Controllers\Physical\RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/{entity}/rooms/{room}', [\App\Http\Controllers\Physical\RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/{entity}/rooms/{room}', [\App\Http\Controllers\Physical\RoomController::class, 'destroy'])->name('rooms.destroy');

        // Equipos en habitaciones
        Route::get('/{entity}/rooms/{room}/equipment', [\App\Http\Controllers\Physical\RoomEquipmentController::class, 'dashboard'])->name('rooms.equipment');
        Route::post('/{entity}/rooms/{room}/equipment', [\App\Http\Controllers\Physical\RoomEquipmentController::class, 'store'])->name('rooms.equipment.store');
        Route::get('/{entity}/rooms/{room}/equipment/{equipment}/edit', [\App\Http\Controllers\Physical\RoomEquipmentController::class, 'edit'])->name('rooms.equipment.edit');
        Route::put('/{entity}/rooms/{room}/equipment/{equipment}', [\App\Http\Controllers\Physical\RoomEquipmentController::class, 'update'])->name('rooms.equipment.update');
        Route::delete('/{entity}/rooms/{room}/equipment/{equipment}', [\App\Http\Controllers\Physical\RoomEquipmentController::class, 'destroy'])->name('rooms.equipment.destroy');

        // Ajustes de Uso (contextual a la entidad)
        Route::get('/{entity}/usage-adjustments', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'indexForEntity'])->name('usage_adjustments');
        Route::get('/{entity}/usage-adjustments/{invoice}/edit', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'edit'])->name('usage_adjustments.edit');
        Route::post('/{entity}/usage-adjustments/{invoice}', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'update'])->name('usage_adjustments.update');
        Route::post('/{entity}/usage-adjustments/{invoice}/unlock', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'unlock'])->name('usage_adjustments.unlock');
        Route::get('/{entity}/usage-adjustments/{invoice}', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'show'])->name('usage_adjustments.show');

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
        Route::get('/{entity}/budget', [\App\Http\Controllers\Recommendations\BudgetController::class, 'index'])->name('budget');
        Route::get('/{entity}/solar-water-heater', [\App\Http\Controllers\Recommendations\SolarController::class, 'waterHeater'])->name('solar_water_heater');
        Route::get('/{entity}/standby-analysis', [\App\Http\Controllers\Recommendations\StandbyController::class, 'analysis'])->name('standby_analysis');
        Route::patch('/{entity}/standby-analysis/{equipment}/toggle', [\App\Http\Controllers\Recommendations\StandbyController::class, 'toggle'])->name('standby.toggle');
        Route::get('/{entity}/grid-optimization', [\App\Http\Controllers\Recommendations\GridController::class, 'optimization'])->name('grid_optimization');
        Route::get('/{entity}/smart-meter-demo', [\App\Http\Controllers\Consumption\SmartMeterController::class, 'demo'])->name('smart_meter_demo');
        Route::get('/{entity}/replacements', [\App\Http\Controllers\Recommendations\ReplacementController::class, 'index'])->name('replacements');
        Route::get('/{entity}/maintenance', [\App\Http\Controllers\Recommendations\MaintenanceController::class, 'index'])->name('maintenance');

        // Salud Térmica
        Route::get('/{entity}/thermal', [\App\Http\Controllers\Recommendations\ThermalComfortController::class, 'index'])->name('thermal');
        Route::get('/{entity}/thermal/wizard', [\App\Http\Controllers\Recommendations\ThermalComfortController::class, 'wizard'])->name('thermal.wizard');
        Route::post('/{entity}/thermal', [\App\Http\Controllers\Recommendations\ThermalComfortController::class, 'store'])->name('thermal.store');
        Route::get('/{entity}/thermal/result', [\App\Http\Controllers\Recommendations\ThermalComfortController::class, 'result'])->name('thermal.result');

        // Medidor/Contrato
        Route::get('/{entity}/meter', [\App\Http\Controllers\Physical\ContractController::class, 'showForEntity'])->name('meter');

        // Facturas
        Route::get('/{entity}/invoices', [\App\Http\Controllers\Physical\InvoiceController::class, 'index'])->name('invoices');
        Route::get('/{entity}/invoices/create', [\App\Http\Controllers\Physical\InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/{entity}/invoices', [\App\Http\Controllers\Physical\InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/{entity}/invoices/{invoice}/edit', [\App\Http\Controllers\Physical\InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('/{entity}/invoices/{invoice}', [\App\Http\Controllers\Physical\InvoiceController::class, 'update'])->name('invoices.update');

        // Habitaciones
        Route::get('/{entity}/rooms', [\App\Http\Controllers\Physical\RoomController::class, 'index'])->name('rooms');
        Route::get('/{entity}/rooms/create', [\App\Http\Controllers\Physical\RoomController::class, 'create'])->name('rooms.create');
        Route::post('/{entity}/rooms', [\App\Http\Controllers\Physical\RoomController::class, 'store'])->name('rooms.store');
        Route::get('/{entity}/rooms/{room}', [\App\Http\Controllers\Physical\RoomController::class, 'show'])->name('rooms.show');
        Route::get('/{entity}/rooms/{room}/edit', [\App\Http\Controllers\Physical\RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/{entity}/rooms/{room}', [\App\Http\Controllers\Physical\RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/{entity}/rooms/{room}', [\App\Http\Controllers\Physical\RoomController::class, 'destroy'])->name('rooms.destroy');

        // Equipos en habitaciones
        Route::get('/{entity}/rooms/{room}/equipment', [\App\Http\Controllers\Physical\RoomEquipmentController::class, 'dashboard'])->name('rooms.equipment');
        Route::post('/{entity}/rooms/{room}/equipment', [\App\Http\Controllers\Physical\RoomEquipmentController::class, 'store'])->name('rooms.equipment.store');
        Route::get('/{entity}/rooms/{room}/equipment/{equipment}/edit', [\App\Http\Controllers\Physical\RoomEquipmentController::class, 'edit'])->name('rooms.equipment.edit');
        Route::put('/{entity}/rooms/{room}/equipment/{equipment}', [\App\Http\Controllers\Physical\RoomEquipmentController::class, 'update'])->name('rooms.equipment.update');
        Route::delete('/{entity}/rooms/{room}/equipment/{equipment}', [\App\Http\Controllers\Physical\RoomEquipmentController::class, 'destroy'])->name('rooms.equipment.destroy');

        // Ajustes de Uso (contextual a la entidad)
        Route::get('/{entity}/usage-adjustments', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'indexForEntity'])->name('usage_adjustments');
        Route::get('/{entity}/usage-adjustments/{invoice}/edit', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'edit'])->name('usage_adjustments.edit');
        Route::post('/{entity}/usage-adjustments/{invoice}', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'update'])->name('usage_adjustments.update');
        Route::post('/{entity}/usage-adjustments/{invoice}/unlock', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'unlock'])->name('usage_adjustments.unlock');
        Route::get('/{entity}/usage-adjustments/{invoice}', [\App\Http\Controllers\Consumption\UsageAdjustmentController::class, 'show'])->name('usage_adjustments.show');

        // Panel de Consumo (contextual a la entidad)
        Route::get('/{entity}/consumption', [\App\Http\Controllers\Consumption\PanelController::class, 'showForEntity'])->name('consumption');
        Route::get('/{entity}/consumption/{invoice}', [\App\Http\Controllers\Consumption\PanelController::class, 'show'])->name('consumption.show');
    });

    // =====================================================
    // RUTAS LEGACY (mantener compatibilidad temporal)
    // =====================================================
    Route::get('/entities', [\App\Http\Controllers\EntityController::class, 'index'])->name('entities.index');
    Route::get('/entities/create', [\App\Http\Controllers\EntityController::class, 'create'])->name('entities.create');
    Route::post('/entities', [\App\Http\Controllers\EntityController::class, 'store'])->name('entities.store');
    Route::get('/entities/{entity}', [\App\Http\Controllers\EntityController::class, 'show'])->name('entities.show');
    Route::get('/entities/{entity}/edit', [\App\Http\Controllers\EntityController::class, 'edit'])->name('entities.edit');
    Route::put('/entities/{entity}', [\App\Http\Controllers\EntityController::class, 'update'])->name('entities.update');
    Route::delete('/entities/{entity}', [\App\Http\Controllers\EntityController::class, 'destroy'])->name('entities.destroy');

    // Legacy routes anidadas (para compatibilidad mientras se migra)
    Route::get('/entities/{entity}/budget', [\App\Http\Controllers\EntityController::class, 'budget'])->name('entities.budget');
    Route::get('/entities/{entity}/solar-water-heater', [\App\Http\Controllers\EntityController::class, 'solarWaterHeater'])->name('entities.solar_water_heater');
    Route::get('/entities/{entity}/standby-analysis', [\App\Http\Controllers\EntityController::class, 'standbyAnalysis'])->name('entities.standby_analysis');
    Route::patch('/entities/{entity}/standby-analysis/{equipment}/toggle', [\App\Http\Controllers\EntityController::class, 'toggleStandby'])->name('entities.standby.toggle');
    Route::get('/entities/{entity}/vacation', [\App\Http\Controllers\VacationController::class, 'index'])->name('vacation.index');
    Route::post('/entities/{entity}/vacation', [\App\Http\Controllers\VacationController::class, 'calculate'])->name('vacation.calculate');
    Route::post('/entities/{entity}/vacation/confirm', [\App\Http\Controllers\VacationController::class, 'confirm'])->name('vacation.confirm');
    Route::get('/entities/{entity}/grid-optimization', [\App\Http\Controllers\EntityController::class, 'gridOptimization'])->name('grid.optimization');
    Route::get('/entities/{entity}/smart-meter-demo', [\App\Http\Controllers\SmartMeterController::class, 'demo'])->name('smart_meter.demo');
    Route::get('/entities/{entity}/rooms', [\App\Http\Controllers\RoomController::class, 'index'])->name('rooms.index');
    Route::get('/entities/{entity}/rooms/create', [\App\Http\Controllers\RoomController::class, 'create'])->name('rooms.create');
    Route::post('/entities/{entity}/rooms', [\App\Http\Controllers\RoomController::class, 'store'])->name('rooms.store');
    Route::get('/entities/{entity}/rooms/{room}', [\App\Http\Controllers\RoomController::class, 'show'])->name('rooms.show');
    Route::get('/entities/{entity}/rooms/{room}/edit', [\App\Http\Controllers\RoomController::class, 'edit'])->name('rooms.edit');
    Route::put('/entities/{entity}/rooms/{room}', [\App\Http\Controllers\RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/entities/{entity}/rooms/{room}', [\App\Http\Controllers\RoomController::class, 'destroy'])->name('rooms.destroy');
    Route::get('/entities/{entity}/rooms/{room}/equipment', [\App\Http\Controllers\RoomEquipmentController::class, 'dashboard'])->name('rooms.equipment.dashboard');
    Route::post('/entities/{entity}/rooms/{room}/equipment', [\App\Http\Controllers\RoomEquipmentController::class, 'store'])->name('rooms.equipment.store');
    Route::get('/entities/{entity}/rooms/{room}/equipment/{equipment}/edit', [\App\Http\Controllers\RoomEquipmentController::class, 'edit'])->name('rooms.equipment.edit');
    Route::put('/entities/{entity}/rooms/{room}/equipment/{equipment}', [\App\Http\Controllers\RoomEquipmentController::class, 'update'])->name('rooms.equipment.update');
    Route::delete('/entities/{entity}/rooms/{room}/equipment/{equipment}', [\App\Http\Controllers\RoomEquipmentController::class, 'destroy'])->name('rooms.equipment.destroy');
    Route::get('/entities/{entity}/meter', [\App\Http\Controllers\ContractController::class, 'showForEntity'])->name('entities.meter.index');
    Route::get('/entities/{entity}/invoices', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('entities.invoices.index');
    Route::get('/entities/{entity}/invoices/create', [\App\Http\Controllers\InvoiceController::class, 'create'])->name('entities.invoices.create');
    Route::post('/entities/{entity}/invoices', [\App\Http\Controllers\InvoiceController::class, 'store'])->name('entities.invoices.store');
    Route::get('/entities/{entity}/invoices/{invoice}/edit', [\App\Http\Controllers\InvoiceController::class, 'edit'])->name('entities.invoices.edit');
    Route::put('/entities/{entity}/invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'update'])->name('entities.invoices.update');
    Route::get('/entities/{entity}/maintenance', [\App\Http\Controllers\MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::get('/entities/{entity}/replacements', [\App\Http\Controllers\ReplacementController::class, 'index'])->name('replacements.index');
    Route::get('/entities/{entity}/thermal', [\App\Http\Controllers\ThermalComfortController::class, 'index'])->name('thermal.index');
    Route::get('/entities/{entity}/thermal/wizard', [\App\Http\Controllers\ThermalComfortController::class, 'wizard'])->name('thermal.wizard');
    Route::post('/entities/{entity}/thermal', [\App\Http\Controllers\ThermalComfortController::class, 'store'])->name('thermal.store');
    Route::get('/entities/{entity}/thermal/result', [\App\Http\Controllers\ThermalComfortController::class, 'result'])->name('thermal.result');
});

// =====================================================
// SUPER ADMIN ROUTES
// =====================================================
Route::middleware(['auth', 'super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\SuperAdminController::class, 'dashboard'])->name('dashboard');
});
