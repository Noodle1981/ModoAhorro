<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\InfrastructureController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MiningDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\Recommendations\ThermalComfortController;
use App\Http\Controllers\UnificationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->is_super_admin) {
            return redirect()->route('sistema.admin');
        }

        return redirect()->route('dashboard');
    }

    return view('welcome');
});

// Autenticación
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login'])->middleware('throttle:6,1');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Rutas Protegidas
Route::middleware(['auth'])->group(function () {
    // Entidades (Selector)
    Route::get('/entidades', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/entidades/nueva', [EntityController::class, 'store'])->name('entities.store');
    Route::delete('/entidades/{entity}', [EntityController::class, 'destroy'])->name('entities.destroy');
    Route::redirect('/dashboard', '/entidades');

    // Inicio / Resumen de Entidad (Panel con Sidebar)
    Route::get('/inicio', [DashboardController::class, 'home'])->name('home');

    // Dashboard Minero (Campamentos y Alta Montaña)
    Route::get('/mineria/dashboard', [MiningDashboardController::class, 'index'])->name('mining.dashboard');

    // Perfil de Usuario
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/perfil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Activar una entidad específica
    Route::get('/entidades/{entity}/activate', function ($entityId) {
        session(['active_entity_id' => $entityId]);

        return redirect()->route('home');
    })->name('entities.activate');

    // Grupos Funcionales
    Route::prefix('gestion')->name('gestion.')->group(function () {
        // Contratos
        Route::get('/contratos', [ContractController::class, 'index'])->name('contracts');
        Route::post('/contratos', [ContractController::class, 'store'])->name('contracts.store');
        Route::put('/contratos/{contract}', [ContractController::class, 'update'])->name('contracts.update');
        Route::delete('/contratos/{contract}', [ContractController::class, 'destroy'])->name('contracts.destroy');
        Route::patch('/contratos/{contract}/toggle', [ContractController::class, 'toggleActive'])->name('contracts.toggle');

        // Módulo Térmico
        Route::prefix('thermal')->name('thermal.')->group(function () {
            Route::get('/{entity}', [ThermalComfortController::class, 'index'])->name('index');
            Route::get('/wizard/{entity?}', [ThermalComfortController::class, 'wizard'])->name('wizard');
            Route::get('/result/{entity?}', [ThermalComfortController::class, 'result'])->name('result');
            Route::post('/wizard/{entity?}', [ThermalComfortController::class, 'store'])->name('store');
        });

        Route::get('/facturas', [InvoiceController::class, 'index'])->name('invoices');
        Route::post('/facturas', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::put('/facturas/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('/facturas/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::get('/unificaciones', [UnificationController::class, 'index'])->name('unifications');

        Route::get('/infraestructura', [InfrastructureController::class, 'index'])->name('infrastructure');

        // Perfil de la Entidad (Mi Casa)
        Route::get('/entidad/perfil', [EntityController::class, 'edit'])->name('entity.edit');
        Route::put('/entidad/perfil', [EntityController::class, 'update'])->name('entity.update');

        // Rooms
        Route::post('/ambientes', [InfrastructureController::class, 'storeRoom'])->name('rooms.store');
        Route::put('/ambientes/{room}', [InfrastructureController::class, 'updateRoom'])->name('rooms.update');
        Route::delete('/ambientes/{room}', [InfrastructureController::class, 'destroyRoom'])->name('rooms.destroy');

        // Equipment
        Route::post('/equipos', [InfrastructureController::class, 'storeEquipment'])->name('equipment.store');
        Route::put('/equipos/{equipment}', [InfrastructureController::class, 'updateEquipment'])->name('equipment.update');
        Route::delete('/equipos/{equipment}', [InfrastructureController::class, 'destroyEquipment'])->name('equipment.destroy');
    });

    Route::prefix('analisis')->name('analisis.')->group(function () {
        Route::get('/consumo-real', [AnalysisController::class, 'realConsumption'])->name('consumption');
        Route::get('/tiempo', [AnalysisController::class, 'timeAnalysis'])->name('time');
        Route::get('/coste-equipo', [AnalysisController::class, 'equipmentCost'])->name('equipment-cost');
        Route::get('/ajuste-uso', [AnalysisController::class, 'usageAdjustment'])->name('usage');
        Route::get('/ajuste-uso/detalle/{contract}/{start_date}/{end_date}', [AnalysisController::class, 'usageAdjustmentDetail'])->name('usage.detail');
        Route::post('/ajuste-uso/guardar-detalle', [AnalysisController::class, 'saveContextOnly'])->name('usage.save');
        Route::post('/ajuste-uso/sintonizar', [AnalysisController::class, 'calibrateAndShowResults'])->name('usage.calibrate');
        Route::get('/ajuste-uso/{invoice}/resultados', [AnalysisController::class, 'showEngineResults'])->name('usage.results');
    });

    Route::prefix('recomendaciones')->name('recomendaciones.')->group(function () {
        Route::get('/solar', [RecommendationController::class, 'solar'])->name('solar');
        Route::get('/reemplazos', [RecommendationController::class, 'replacements'])->name('replacements');
        Route::get('/consumo-fantasma', [RecommendationController::class, 'standby'])->name('standby');
        Route::post('/consumo-fantasma/{equipment}/toggle', [RecommendationController::class, 'toggleStandby'])->name('standby.toggle');
        Route::get('/salud-termica', [RecommendationController::class, 'thermalHealth'])->name('thermal-health');
        Route::get('/mantenimiento', [RecommendationController::class, 'maintenance'])->name('maintenance');
        Route::get('/vacaciones', [RecommendationController::class, 'vacation'])->name('vacation');
        Route::get('/optimizacion-horarios', [RecommendationController::class, 'gridOptimization'])->name('grid-optimization');
    });

    Route::prefix('sistema')->name('sistema.')->group(function () {
        // Autocompletar disponible para cualquier usuario autenticado (usado en inventario de equipos)
        Route::get('/api/modelos-autocompletar', [AdminController::class, 'autocompleteModels'])->name('models.autocomplete');

        // Rutas estrictamente administrativas protegidas con middleware admin
        Route::middleware(['admin'])->group(function () {
            Route::get('/administracion', [AdminController::class, 'index'])->name('admin');

            // Catálogo Maestro (Equipment Types)
            Route::get('/catalogo', [AdminController::class, 'equipmentTypes'])->name('catalogue');
            Route::post('/catalogo', [AdminController::class, 'storeEquipmentType'])->name('catalogue.store');
            Route::put('/catalogo/{equipmentType}', [AdminController::class, 'updateEquipmentType'])->name('catalogue.update');
            Route::patch('/catalogo/{equipmentType}/toggle', [AdminController::class, 'toggleActiveEquipmentType'])->name('catalogue.toggle');
            Route::delete('/catalogo/{equipmentType}', [AdminController::class, 'destroyEquipmentType'])->name('catalogue.destroy');

            // Matriz de Eficiencia Energética
            Route::get('/eficiencia', [AdminController::class, 'efficiencyLabels'])->name('efficiency');
            Route::post('/eficiencia', [AdminController::class, 'storeEfficiencyLabel'])->name('efficiency.store');
            Route::put('/eficiencia/{coefficient}', [AdminController::class, 'updateEfficiencyLabel'])->name('efficiency.update');
            Route::delete('/eficiencia/{coefficient}', [AdminController::class, 'destroyEfficiencyLabel'])->name('efficiency.destroy');
            Route::post('/eficiencia/restablecer', [AdminController::class, 'resetEfficiencyLabels'])->name('efficiency.reset');

            // Benchmarks de Eficiencia (Estándares de Referencia y Reemplazos)
            Route::get('/benchmarks', [AdminController::class, 'benchmarks'])->name('benchmarks');
            Route::post('/benchmarks', [AdminController::class, 'storeBenchmark'])->name('benchmarks.store');
            Route::put('/benchmarks/{benchmark}', [AdminController::class, 'updateBenchmark'])->name('benchmarks.update');
            Route::delete('/benchmarks/{benchmark}', [AdminController::class, 'destroyBenchmark'])->name('benchmarks.destroy');

            // Modelos de Mercado & Inteligencia de Clientes
            Route::get('/modelos', [AdminController::class, 'equipmentModels'])->name('models');
            Route::post('/modelos', [AdminController::class, 'storeEquipmentModel'])->name('models.store');
            Route::put('/modelos/{equipmentModel}', [AdminController::class, 'updateEquipmentModel'])->name('models.update');
            Route::delete('/modelos/{equipmentModel}', [AdminController::class, 'destroyEquipmentModel'])->name('models.destroy');

            // Gestión de Usuarios, Reseteos & Pagos
            Route::get('/usuarios', [AdminController::class, 'users'])->name('users');
            Route::post('/usuarios', [AdminController::class, 'storeUser'])->name('users.store');
            Route::put('/usuarios/{user}', [AdminController::class, 'updateUser'])->name('users.update');
            Route::patch('/usuarios/{user}/toggle-admin', [AdminController::class, 'toggleAdminUser'])->name('users.toggle-admin');
            Route::delete('/usuarios/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');

            // Reseteos de Contraseña
            Route::get('/usuarios/reseteos', [AdminController::class, 'userResets'])->name('users.resets');
            Route::post('/usuarios/reseteos/generar', [AdminController::class, 'generateResetLink'])->name('users.resets.generate');
            Route::post('/usuarios/reseteos/forzar', [AdminController::class, 'forceResetPassword'])->name('users.resets.force');
            Route::delete('/usuarios/reseteos/{email}', [AdminController::class, 'destroyResetToken'])->name('users.resets.destroy');

            // Pagos & Suscripciones
            Route::get('/usuarios/pagos', [AdminController::class, 'userPayments'])->name('users.payments');
            Route::post('/usuarios/pagos/asignar-plan', [AdminController::class, 'assignPlan'])->name('users.payments.assign');
            Route::post('/usuarios/pagos/extender', [AdminController::class, 'extendSubscription'])->name('users.payments.extend');
            Route::put('/usuarios/pagos/planes/{plan}', [AdminController::class, 'updatePlan'])->name('users.plans.update');

            // APIs & Integraciones
            Route::get('/apis', [AdminController::class, 'apis'])->name('apis');
            Route::post('/apis', [AdminController::class, 'updateApis'])->name('apis.update');
        });
    });
});
