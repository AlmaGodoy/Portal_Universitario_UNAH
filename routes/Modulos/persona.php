<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonaController;

/*
|--------------------------------------------------------------------------
| Rutas del Módulo de Persona
|--------------------------------------------------------------------------
*/

// Ruta para agregar una nueva persona
Route::post('/persona/agregar', [PersonaController::class, 'agregar']);

// Ruta para obtener los datos de una persona por su ID
Route::get('/persona/obtener/{id}', [PersonaController::class, 'obtener']);

// Ruta para actualizar los datos de una persona
Route::put('/persona/actualizar/{id}', [PersonaController::class, 'actualizar']);

// Ruta para eliminar (borrado lógico o físico) una persona
Route::delete('/persona/eliminar/{id}', [PersonaController::class, 'eliminar']);
