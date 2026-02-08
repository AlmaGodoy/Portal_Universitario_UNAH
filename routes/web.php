<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DocumentoExcepcionalController;

// Rutas para documentos excepcionales
Route::post('cancelaciones-excepcionales', [DocumentoExcepcionalController::class, 'insertar']);
Route::get('cancelaciones-excepcionales/{id}', [DocumentoExcepcionalController::class, 'obtener']);
Route::delete('cancelaciones-excepcionales/{id}', [DocumentoExcepcionalController::class, 'eliminar']);
