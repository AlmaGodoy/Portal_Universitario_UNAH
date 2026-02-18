<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DocumentoExcepcionalController;
use App\Http\Controllers\CambioCarreraController;
use App\Http\Controllers\HistorialAcademicoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\Emitir_ResolucionesController;
use App\Http\Controllers\ValidarDocumentoController;

// Rutas de API agrupadas
Route::prefix('api')->group(function () {
    Route::post('cancelaciones/crear', [DocumentoExcepcionalController::class, 'subir']);
    Route::get('cancelaciones/todas', [DocumentoExcepcionalController::class, 'obtenerTodos']);
    Route::get('cancelaciones/detalle/{id}', [DocumentoExcepcionalController::class, 'obtenerCancelacion']);
    Route::delete('cancelaciones/eliminar/{id}', [DocumentoExcepcionalController::class, 'eliminar']);
    Route::post('cancelaciones/guardar-documento', [DocumentoExcepcionalController::class, 'guardarDocumento']);
    Route::put('cancelaciones/actualizar/{id}', [DocumentoExcepcionalController::class, 'actualizar']);

    // VALIDAR DOCUMENTO
    Route::get('pendientes', [ValidarDocumentoController::class, 'listarPendientes']);
    Route::post('aprobar', [ValidarDocumentoController::class, 'aprobar']);
    Route::post('devolver', [ValidarDocumentoController::class, 'devolver']);

    // CAMBIO DE CARRERA
    Route::post('cambio-carrera/crear', [CambioCarreraController::class, 'crear']);
    Route::get('cambio-carrera/ver/{codigo}', [CambioCarreraController::class, 'ver']);
    Route::put('cambio-carrera/estado/{id_tramite}', [CambioCarreraController::class, 'actualizarEstado']);
    Route::delete('cambio-carrera/eliminar/{id_tramite}', [CambioCarreraController::class, 'eliminar']);

    // HISTORIAL ACADÉMICO
    Route::post('historial/crear', [HistorialAcademicoController::class, 'crear']);
    Route::get('historial/ver/{id_persona}', [HistorialAcademicoController::class, 'ver']);
    Route::put('historial/actualizar/{id_persona}', [HistorialAcademicoController::class, 'actualizar']);
    Route::delete('historial/eliminar/{id_historial}', [HistorialAcademicoController::class, 'eliminar']);

    // Rutas Usuario
    Route::post('/usuarios', [UsuarioController::class, 'crear'])->name('usuarios.store');
    Route::post('/usuarios/{id_persona}/activar', [UsuarioController::class, 'activar'])->name('usuarios.activar');
    Route::post('/usuarios/{id_persona}/desactivar', [UsuarioController::class, 'desactivar'])->name('usuarios.desactivar');
    Route::post('/usuarios/{id_usuario}/rol', [UsuarioController::class, 'asignarRol'])->name('usuarios.rol');

    // Rutas pago
    Route::post('pagos/crear', [PagoController::class, 'crear']);
    Route::get('pagos/ver/{id_tramite}', [PagoController::class, 'verPorTramite']);
    Route::put('pagos/estado/{id_pago}', [PagoController::class, 'actualizarEstado']);

    // Rutas para Subir Documento
    Route::post('documentos/crear', [DocumentoController::class, 'crear']);
    Route::get('documentos/ver/{id_tramite}', [DocumentoController::class, 'ver']);
    Route::put('documentos/actualizar/{id_documento}', [DocumentoController::class, 'actualizar']);
    Route::delete('documentos/eliminar/{id_documento}', [DocumentoController::class, 'eliminar']);

    // RUTAS GESTIONAR PERSONA
    Route::post('/persona', [PersonaController::class, 'gestionarPersona']);
    Route::get('/persona/{id}', [PersonaController::class, 'obtenerPersona']);
    Route::delete('/persona/{id}', [PersonaController::class, 'eliminarPersona']);

    // RUTAS EMITIR RESOLUCION
    Route::post('/resolucion', [Emitir_ResolucionesController::class, 'emitirResolucion']);
    Route::get('/resolucion/{id}', [Emitir_ResolucionesController::class, 'obtenerResolucion']);
    Route::delete('/resolucion/{id}', [Emitir_ResolucionesController::class, 'eliminarResolucion']);
});

// Rutas web
Auth::routes([
    'register' => false,
    'reset' => false,
    'verify' => false,
]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/register', [UsuarioController::class, 'formRegistro'])->name('register');
Route::post('/register', [UsuarioController::class, 'crearWeb'])->name('register.store');
