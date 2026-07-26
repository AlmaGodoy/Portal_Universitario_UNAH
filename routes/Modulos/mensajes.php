<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MensajeController;

/*
|--------------------------------------------------------------------------
| MÓDULO DE MENSAJES INTERNOS
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'session.timeout',
])->prefix('mensajes')->name('mensajes.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | BANDEJA DE ENTRADA
    |--------------------------------------------------------------------------
    */

    Route::get('/', [MensajeController::class, 'index'])
        ->name('index');

    /*
    |--------------------------------------------------------------------------
    | MENSAJES ENVIADOS
    |--------------------------------------------------------------------------
    */

    Route::get('/enviados', [MensajeController::class, 'enviados'])
        ->name('enviados');

    /*
    |--------------------------------------------------------------------------
    | CREAR MENSAJE
    |--------------------------------------------------------------------------
    */

    Route::get('/crear', [MensajeController::class, 'create'])
        ->name('create');

    /*
    |--------------------------------------------------------------------------
    | GUARDAR MENSAJE
    |--------------------------------------------------------------------------
    */

    Route::post('/', [MensajeController::class, 'store'])
        ->name('store');

    /*
    |--------------------------------------------------------------------------
    | VER CONVERSACIÓN
    |--------------------------------------------------------------------------
    | Esta ruta debe ir después de /enviados y /crear para evitar conflictos.
    */

    Route::get('/{idMensaje}', [MensajeController::class, 'show'])
        ->whereNumber('idMensaje')
        ->name('show');

    /*
    |--------------------------------------------------------------------------
    | RESPONDER MENSAJE
    |--------------------------------------------------------------------------
    */

    Route::post('/{idMensaje}/responder', [MensajeController::class, 'responder'])
        ->whereNumber('idMensaje')
        ->name('responder');

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR MENSAJE DE LA BANDEJA DEL USUARIO
    |--------------------------------------------------------------------------
    */

    Route::delete('/{idMensaje}', [MensajeController::class, 'destroy'])
        ->whereNumber('idMensaje')
        ->name('destroy');
});


/*
|--------------------------------------------------------------------------
| API DE MENSAJES PARA EL TOPBAR
|--------------------------------------------------------------------------
| Esta ruta se deja fuera del prefijo /mensajes para que funcione exactamente
| como la está llamando el layout: /api/mensajes/recientes
*/

Route::middleware([
    'auth',
    'session.timeout',
])->prefix('api/mensajes')->name('api.mensajes.')->group(function () {

    Route::get('/recientes', [MensajeController::class, 'recientes'])
        ->name('recientes');
});