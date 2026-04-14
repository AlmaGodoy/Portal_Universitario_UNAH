<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CancelacionSecretariaController;

Route::middleware(['auth'])
    ->prefix('empleado/secretaria/cancelacion')
    ->name('cancelacion.secretaria.')
    ->controller(CancelacionSecretariaController::class)
    ->group(function () {

        // Bandeja principal de revisión documental
        Route::get('/', 'index')
            ->name('index');

        // Ver documento adjunto
        Route::get('/documento/{id_documento}', 'verDocumento')
            ->whereNumber('id_documento')
            ->name('documento');

        // Devolver documentación al estudiante
        Route::post('/{id_tramite}/devolver', 'devolverDocumentacion')
            ->whereNumber('id_tramite')
            ->name('devolver');

        // Marcar trámite listo para coordinadora
        Route::post('/{id_tramite}/listo', 'marcarListoParaCoordinadora')
            ->whereNumber('id_tramite')
            ->name('listo');

        // Ver detalle del trámite
        Route::get('/{id_tramite}', 'detalle')
            ->whereNumber('id_tramite')
            ->name('detalle');
    });