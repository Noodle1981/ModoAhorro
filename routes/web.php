<?php

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
Route::get('login', 'App\Http\Controllers\Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'App\Http\Controllers\Auth\LoginController@login');
Route::post('logout', 'App\Http\Controllers\Auth\LoginController@logout')->name('logout');

// Rutas Protegidas
Route::middleware(['auth'])->group(function () {
    // Entidades (Selector)
    Route::get('/entidades', 'App\Http\Controllers\DashboardController@index')->name('dashboard');
    Route::post('/entidades/nueva', 'App\Http\Controllers\EntityController@store')->name('entities.store');
    Route::delete('/entidades/{entity}', 'App\Http\Controllers\EntityController@destroy')->name('entities.destroy');
    Route::redirect('/dashboard', '/entidades');

    // Inicio / Resumen de Entidad (Panel con Sidebar)
    Route::get('/inicio', 'App\Http\Controllers\DashboardController@home')->name('home');

    // Perfil de Usuario
    Route::get('/perfil', 'App\Http\Controllers\ProfileController@edit')->name('profile.edit');
    Route::put('/perfil', 'App\Http\Controllers\ProfileController@update')->name('profile.update');
    Route::put('/perfil/password', 'App\Http\Controllers\ProfileController@updatePassword')->name('profile.password');

    // Activar una entidad específica
    Route::get('/entidades/{entity}/activate', function ($entityId) {
        session(['active_entity_id' => $entityId]);

        return redirect()->route('home');
    })->name('entities.activate');

    // Grupos Funcionales
    Route::prefix('gestion')->name('gestion.')->group(function () {
        // Contratos
        Route::get('/contratos', 'App\Http\Controllers\ContractController@index')->name('contracts');
        Route::post('/contratos', 'App\Http\Controllers\ContractController@store')->name('contracts.store');
        Route::put('/contratos/{contract}', 'App\Http\Controllers\ContractController@update')->name('contracts.update');
        Route::delete('/contratos/{contract}', 'App\Http\Controllers\ContractController@destroy')->name('contracts.destroy');
        Route::patch('/contratos/{contract}/toggle', 'App\Http\Controllers\ContractController@toggleActive')->name('contracts.toggle');

        // Módulo Térmico
        Route::prefix('thermal')->name('thermal.')->group(function () {
            Route::get('/{entity}', 'App\Http\Controllers\Recommendations\ThermalComfortController@index')->name('index');
            Route::get('/wizard/{entity?}', 'App\Http\Controllers\Recommendations\ThermalComfortController@wizard')->name('wizard');
            Route::get('/result/{entity?}', 'App\Http\Controllers\Recommendations\ThermalComfortController@result')->name('result');
            Route::post('/wizard/{entity?}', 'App\Http\Controllers\Recommendations\ThermalComfortController@store')->name('store');
        });

        Route::get('/facturas', 'App\Http\Controllers\InvoiceController@index')->name('invoices');
        Route::post('/facturas', 'App\Http\Controllers\InvoiceController@store')->name('invoices.store');
        Route::put('/facturas/{invoice}', 'App\Http\Controllers\InvoiceController@update')->name('invoices.update');
        Route::delete('/facturas/{invoice}', 'App\Http\Controllers\InvoiceController@destroy')->name('invoices.destroy');
        Route::get('/unificaciones', 'App\Http\Controllers\UnificationController@index')->name('unifications');

        Route::get('/infraestructura', 'App\Http\Controllers\InfrastructureController@index')->name('infrastructure');

        // Perfil de la Entidad (Mi Casa)
        Route::get('/entidad/perfil', 'App\Http\Controllers\EntityController@edit')->name('entity.edit');
        Route::put('/entidad/perfil', 'App\Http\Controllers\EntityController@update')->name('entity.update');

        // Rooms
        Route::post('/ambientes', 'App\Http\Controllers\InfrastructureController@storeRoom')->name('rooms.store');
        Route::put('/ambientes/{room}', 'App\Http\Controllers\InfrastructureController@updateRoom')->name('rooms.update');
        Route::delete('/ambientes/{room}', 'App\Http\Controllers\InfrastructureController@destroyRoom')->name('rooms.destroy');

        // Equipment
        Route::post('/equipos', 'App\Http\Controllers\InfrastructureController@storeEquipment')->name('equipment.store');
        Route::put('/equipos/{equipment}', 'App\Http\Controllers\InfrastructureController@updateEquipment')->name('equipment.update');
        Route::delete('/equipos/{equipment}', 'App\Http\Controllers\InfrastructureController@destroyEquipment')->name('equipment.destroy');
    });

    Route::prefix('analisis')->name('analisis.')->group(function () {
        Route::get('/consumo-real', 'App\Http\Controllers\AnalysisController@realConsumption')->name('consumption');
        Route::get('/tiempo', 'App\Http\Controllers\AnalysisController@timeAnalysis')->name('time');
        Route::get('/coste-equipo', 'App\Http\Controllers\AnalysisController@equipmentCost')->name('equipment-cost');
        Route::get('/ajuste-uso', 'App\Http\Controllers\AnalysisController@usageAdjustment')->name('usage');
        Route::get('/ajuste-uso/detalle/{contract}/{start_date}/{end_date}', 'App\Http\Controllers\AnalysisController@usageAdjustmentDetail')->name('usage.detail');
        Route::post('/ajuste-uso/guardar-detalle', 'App\Http\Controllers\AnalysisController@saveContextOnly')->name('usage.save');
        Route::post('/ajuste-uso/sintonizar', 'App\Http\Controllers\AnalysisController@calibrateAndShowResults')->name('usage.calibrate');
        Route::get('/ajuste-uso/{invoice}/resultados', 'App\Http\Controllers\AnalysisController@showEngineResults')->name('usage.results');
    });

    Route::prefix('recomendaciones')->name('recomendaciones.')->group(function () {
        Route::get('/solar', 'App\Http\Controllers\RecommendationController@solar')->name('solar');
        Route::get('/reemplazos', 'App\Http\Controllers\RecommendationController@replacements')->name('replacements');
        Route::get('/consumo-fantasma', 'App\Http\Controllers\RecommendationController@standby')->name('standby');
        Route::post('/consumo-fantasma/{equipment}/toggle', 'App\Http\Controllers\RecommendationController@toggleStandby')->name('standby.toggle');
        Route::get('/salud-termica', 'App\Http\Controllers\RecommendationController@thermalHealth')->name('thermal-health');
        Route::get('/mantenimiento', 'App\Http\Controllers\RecommendationController@maintenance')->name('maintenance');
        Route::get('/vacaciones', 'App\Http\Controllers\RecommendationController@vacation')->name('vacation');
        Route::get('/optimizacion-horarios', 'App\Http\Controllers\RecommendationController@gridOptimization')->name('grid-optimization');
    });

    Route::prefix('sistema')->name('sistema.')->group(function () {
        Route::get('/administracion', 'App\Http\Controllers\AdminController@index')->name('admin');
        
        // Catálogo Maestro (Equipment Types)
        Route::get('/catalogo', 'App\Http\Controllers\AdminController@equipmentTypes')->name('catalogue');
        Route::post('/catalogo', 'App\Http\Controllers\AdminController@storeEquipmentType')->name('catalogue.store');
        Route::put('/catalogo/{equipmentType}', 'App\Http\Controllers\AdminController@updateEquipmentType')->name('catalogue.update');
        Route::patch('/catalogo/{equipmentType}/toggle', 'App\Http\Controllers\AdminController@toggleActiveEquipmentType')->name('catalogue.toggle');
        Route::delete('/catalogo/{equipmentType}', 'App\Http\Controllers\AdminController@destroyEquipmentType')->name('catalogue.destroy');

        // Matriz de Eficiencia Energética
        Route::get('/eficiencia', 'App\Http\Controllers\AdminController@efficiencyLabels')->name('efficiency');
        Route::post('/eficiencia', 'App\Http\Controllers\AdminController@storeEfficiencyLabel')->name('efficiency.store');
        Route::put('/eficiencia/{coefficient}', 'App\Http\Controllers\AdminController@updateEfficiencyLabel')->name('efficiency.update');
        Route::delete('/eficiencia/{coefficient}', 'App\Http\Controllers\AdminController@destroyEfficiencyLabel')->name('efficiency.destroy');
        Route::post('/eficiencia/restablecer', 'App\Http\Controllers\AdminController@resetEfficiencyLabels')->name('efficiency.reset');

        // Benchmarks de Eficiencia (Estándares de Referencia y Reemplazos)
        Route::get('/benchmarks', 'App\Http\Controllers\AdminController@benchmarks')->name('benchmarks');
        Route::post('/benchmarks', 'App\Http\Controllers\AdminController@storeBenchmark')->name('benchmarks.store');
        Route::put('/benchmarks/{benchmark}', 'App\Http\Controllers\AdminController@updateBenchmark')->name('benchmarks.update');
        Route::delete('/benchmarks/{benchmark}', 'App\Http\Controllers\AdminController@destroyBenchmark')->name('benchmarks.destroy');

        // Modelos de Mercado & Inteligencia de Clientes
        Route::get('/modelos', 'App\Http\Controllers\AdminController@equipmentModels')->name('models');
        Route::post('/modelos', 'App\Http\Controllers\AdminController@storeEquipmentModel')->name('models.store');
        Route::put('/modelos/{equipmentModel}', 'App\Http\Controllers\AdminController@updateEquipmentModel')->name('models.update');
        Route::delete('/modelos/{equipmentModel}', 'App\Http\Controllers\AdminController@destroyEquipmentModel')->name('models.destroy');
        Route::get('/api/modelos-autocompletar', 'App\Http\Controllers\AdminController@autocompleteModels')->name('models.autocomplete');

        // Gestión de Usuarios, Reseteos & Pagos
        Route::get('/usuarios', 'App\Http\Controllers\AdminController@users')->name('users');
        Route::post('/usuarios', 'App\Http\Controllers\AdminController@storeUser')->name('users.store');
        Route::put('/usuarios/{user}', 'App\Http\Controllers\AdminController@updateUser')->name('users.update');
        Route::patch('/usuarios/{user}/toggle-admin', 'App\Http\Controllers\AdminController@toggleAdminUser')->name('users.toggle-admin');
        Route::delete('/usuarios/{user}', 'App\Http\Controllers\AdminController@destroyUser')->name('users.destroy');

        // Reseteos de Contraseña
        Route::get('/usuarios/reseteos', 'App\Http\Controllers\AdminController@userResets')->name('users.resets');
        Route::post('/usuarios/reseteos/generar', 'App\Http\Controllers\AdminController@generateResetLink')->name('users.resets.generate');
        Route::post('/usuarios/reseteos/forzar', 'App\Http\Controllers\AdminController@forceResetPassword')->name('users.resets.force');
        Route::delete('/usuarios/reseteos/{email}', 'App\Http\Controllers\AdminController@destroyResetToken')->name('users.resets.destroy');

        // Pagos & Suscripciones
        Route::get('/usuarios/pagos', 'App\Http\Controllers\AdminController@userPayments')->name('users.payments');
        Route::post('/usuarios/pagos/asignar-plan', 'App\Http\Controllers\AdminController@assignPlan')->name('users.payments.assign');
        Route::post('/usuarios/pagos/extender', 'App\Http\Controllers\AdminController@extendSubscription')->name('users.payments.extend');
        Route::put('/usuarios/pagos/planes/{plan}', 'App\Http\Controllers\AdminController@updatePlan')->name('users.plans.update');

        // APIs & Integraciones
        Route::get('/apis', 'App\Http\Controllers\AdminController@apis')->name('apis');
        Route::post('/apis', 'App\Http\Controllers\AdminController@updateApis')->name('apis.update');
    });
});
