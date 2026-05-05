<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->is_super_admin) {
            return redirect()->route('sistema.admin');
        }
        return redirect()->route('dashboard');
    }
    return Inertia::render('Welcome', [
        'laravelVersion' => \Illuminate\Foundation\Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Autenticación
Route::get('login', 'App\Http\Controllers\Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'App\Http\Controllers\Auth\LoginController@login');
Route::post('logout', 'App\Http\Controllers\Auth\LoginController@logout')->name('logout');

// Rutas Protegidas
Route::middleware(['auth'])->group(function () {
    // Entidades (Selector)
    Route::get('/entidades', 'App\Http\Controllers\DashboardController@index')->name('dashboard');
    Route::redirect('/dashboard', '/entidades');

    // Inicio / Resumen de Entidad (Panel con Sidebar)
    Route::get('/inicio', 'App\Http\Controllers\DashboardController@home')->name('home');

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
        Route::get('/catalogo', 'App\Http\Controllers\AdminController@equipmentTypes')->name('catalogue');
        Route::get('/eficiencia', 'App\Http\Controllers\AdminController@efficiencyLabels')->name('efficiency');
        Route::get('/benchmarks', 'App\Http\Controllers\AdminController@benchmarks')->name('benchmarks');
    });
});
