<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentoController;

Route::prefix('api/documentos')->group(function () {
    Route::post('crear', [DocumentoController::class, 'crear']);
    Route::get('ver/{id_tramite}', [DocumentoController::class, 'ver']);
    Route::put('actualizar/{id_documento}', [DocumentoController::class, 'actualizar']);
    Route::delete('eliminar/{id_documento}', [DocumentoController::class, 'eliminar']);
});