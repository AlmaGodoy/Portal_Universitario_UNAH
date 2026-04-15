<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EquivalenciaController;

Route::middleware(['auth', 'session.timeout'])->group(function () {

    Route::get('/equivalencias', [EquivalenciaController::class, 'indexAlumno'])
        ->name('equivalencias.alumno');

    Route::get('/equivalencias/revision', [EquivalenciaController::class, 'indexRevisor'])
        ->name('equivalencias.revisor');

    Route::prefix('api/equivalencias')->name('api.equivalencias.')->group(function () {

        Route::get('/mis-solicitudes', [EquivalenciaController::class, 'misSolicitudes'])
            ->name('mis');

        Route::post('/solicitud', [EquivalenciaController::class, 'crearSolicitud'])
            ->name('crear');

        Route::get('/plan-viejo/{versionPlanViejo}/asignaturas', [EquivalenciaController::class, 'obtenerAsignaturasPlanViejo'])
            ->name('planViejo.asignaturas');

        Route::post('/solicitud/detalle', [EquivalenciaController::class, 'guardarDetalleSolicitud'])
            ->name('detalle.guardar');

        Route::get('/solicitud/{idSolicitud}/cabecera', [EquivalenciaController::class, 'verCabeceraSolicitud'])
            ->name('solicitud.cabecera');

        Route::get('/solicitud/{idSolicitud}/detalle', [EquivalenciaController::class, 'verDetalleSolicitud'])
            ->name('solicitud.detalle');

        Route::get('/solicitud/{idSolicitud}/preliminares', [EquivalenciaController::class, 'verEquivalenciasPreliminares'])
            ->name('solicitud.preliminares');

        Route::get('/solicitud/{idSolicitud}/documento', [EquivalenciaController::class, 'descargarDocumento'])
            ->name('solicitud.documento');

        Route::get('/pendientes', [EquivalenciaController::class, 'solicitudesPendientes'])
            ->name('pendientes');

        Route::post('/solicitud/detalle/validar', [EquivalenciaController::class, 'validarDetalleSolicitud'])
            ->name('detalle.validar');

        Route::post('/solicitud/validar', [EquivalenciaController::class, 'validarSolicitud'])
            ->name('validar');
    });
});
