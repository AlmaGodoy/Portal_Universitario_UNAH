<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonaController;

/*
|--------------------------------------------------------------------------
| Rutas del Módulo de Persona
|--------------------------------------------------------------------------
*/

// Pública solo si se usa durante el registro
Route::post('/persona/agregar', [PersonaController::class, 'agregar']);

// Protegidas
Route::middleware(['auth', 'session.timeout'])->group(function () {
    Route::get('/persona/obtener/{id}', [PersonaController::class, 'obtener']);
    Route::put('/persona/actualizar/{id}', [PersonaController::class, 'actualizar']);
    Route::delete('/persona/eliminar/{id}', [PersonaController::class, 'eliminar']);
});
