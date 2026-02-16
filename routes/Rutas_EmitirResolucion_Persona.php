<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PersonaController;
use App\Http\Controllers\Emitir_ResolucionesController;

/* PERSONA */
Route::post('/persona', [PersonaController::class, 'gestionarPersona']);
Route::get('/persona/{id}', [PersonaController::class, 'obtenerPersona']);
Route::delete('/persona/{id}', [PersonaController::class, 'eliminarPersona']);

/* RESOLUCION */
Route::post('/resolucion', [Emitir_ResolucionesController::class, 'emitirResolucion']);
Route::get('/resolucion/{id}', [Emitir_ResolucionesController::class, 'obtenerResolucion']);
Route::delete('/resolucion/{id}', [Emitir_ResolucionesController::class, 'eliminarResolucion']);
