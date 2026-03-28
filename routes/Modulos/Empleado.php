<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmpleadoController;

/*
|--------------------------------------------------------------------------
| FRONTEND - VISTAS WEB
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/empleado/dashboard', [EmpleadoController::class, 'index'])
        ->name('empleado.dashboard');

});

/*
|--------------------------------------------------------------------------
| API - DATOS JSON
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('api/empleados')->group(function () {

    Route::get('estadisticas', [EmpleadoController::class, 'getEstadisticas'])
        ->name('api.empleados.estadisticas');

    Route::get('listado-por-unidad', [EmpleadoController::class, 'listarPorUnidad'])
        ->name('api.empleados.unidades');

    Route::get('notificaciones', [EmpleadoController::class, 'getNotificaciones'])
        ->name('api.empleados.notificaciones');

});
