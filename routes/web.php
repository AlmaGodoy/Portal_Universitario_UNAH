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
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\Emitir_ResolucionController;
use App\Http\Controllers\ValidarDocumentoController;
use App\Http\Controllers\TramiteController;
use App\Http\Controllers\TramiteControllerAct;
use App\Http\Controllers\ReporteTramiteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\RolController;

/*
|--------------------------------------------------------------------------
| RUTAS API
|--------------------------------------------------------------------------
*/

Route::prefix('api')->group(function () {
    // BITACORA

    Route::get('bitacora/index', [BitacoraController::class, 'index'])->name('bitacora.index');
    Route::get('bitacora/ver/{fecha_inicial}/{fecha_final}', [BitacoraController::class, 'ver'])->name('bitacora.ver');
    Route::get('bitacora/ingresar/{id_usuario}/{id_objeto}/{accion}/{fecha_accion}/{descripcion}', [BitacoraController::class, 'ingresar'])->name('bitacora.ingresar');

    //AUDITORIA
    Route::get('auditoria/ver/{fecha_inicial}/{fecha_final}', [AuditoriaController::class, 'ver']);
    Route::get('auditoria/ingresar/{id_usuario}/{id_objeto}/{accion}/{descripcion}/{fecha}', [AuditoriaController::class, 'ingresar']);


    // TUS RUTAS DE CANCELACIÓN (APIS)
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
    Route::get('cambio-carrera/calendario-vigente', [CambioCarreraController::class, 'calendarioVigente']);
    Route::get('cambio-carrera/carreras', [CambioCarreraController::class, 'carreras']);

    // HISTORIAL
    Route::post('historial/crear', [HistorialAcademicoController::class, 'crear']);
    Route::get('historial/ver/{id_persona}', [HistorialAcademicoController::class, 'ver']);
    Route::put('historial/actualizar/{id_persona}', [HistorialAcademicoController::class, 'actualizar']);
    Route::delete('historial/eliminar/{id_historial}', [HistorialAcademicoController::class, 'eliminar']);

    // USUARIO
    Route::post('/usuarios', [UsuarioController::class, 'crear'])->name('usuarios.store');
    Route::post('/usuarios/{id_persona}/activar', [UsuarioController::class, 'activar'])->name('usuarios.activar');
    Route::post('/usuarios/{id_persona}/desactivar', [UsuarioController::class, 'desactivar'])->name('usuarios.desactivar');
    Route::post('/usuarios/{id_usuario}/rol', [UsuarioController::class, 'asignarRol'])->name('usuarios.rol');

    // PAGOS
    Route::post('pagos/crear', [PagoController::class, 'crear']);
    Route::get('pagos/ver/{id_tramite}', [PagoController::class, 'verPorTramite']);
    Route::put('pagos/estado/{id_pago}', [PagoController::class, 'actualizarEstado']);

    // DOCUMENTOS
    Route::post('documentos/crear', [DocumentoController::class, 'crear']);
    Route::get('documentos/ver/{id_tramite}', [DocumentoController::class, 'ver']);
    Route::put('documentos/actualizar/{id_documento}', [DocumentoController::class, 'actualizar']);
    Route::delete('documentos/eliminar/{id_documento}', [DocumentoController::class, 'eliminar']);

    // PERSONA
    Route::post('/persona/agregar', [PersonaController::class, 'agregar']);
    Route::get('/persona/obtener/{id}', [PersonaController::class, 'obtener']);
    Route::put('/persona/actualizar/{id}', [PersonaController::class, 'actualizar']);
    Route::delete('/persona/eliminar/{id}', [PersonaController::class, 'eliminar']);

    // RESOLUCIÓN
    Route::post('/resolucion/emitir', [Emitir_ResolucionController::class, 'emitir']);
    Route::get('/resolucion/obtener/{id}', [Emitir_ResolucionController::class, 'obtener']);
    Route::put('/resolucion/actualizar/{id}', [Emitir_ResolucionController::class, 'actualizar']);
    Route::delete('/resolucion/eliminar/{id}', [Emitir_ResolucionController::class, 'eliminar']);
    Route::get('/resolucion/listar', [Emitir_ResolucionController::class, 'listar']);

    // TRÁMITES
    Route::post('/tramites/crear', [TramiteController::class, 'crear']);
    Route::put('tramites', [TramiteControllerAct::class, 'actualizar']);
    Route::get('/reporte', [ReporteTramiteController::class, 'reporte']);

    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/register', [UsuarioController::class, 'formRegistro'])->name('register');
    Route::post('/register', [UsuarioController::class, 'crearWeb'])->name('register.store');
});

// Rutas web
Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| PORTAL PRINCIPAL
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => redirect()->route('portal'))->name('root');

Route::get('/portal', fn() => view('auth.choose_portal'))
    ->middleware('guest')
    ->name('portal');

/*
|--------------------------------------------------------------------------
| REGISTRO
|--------------------------------------------------------------------------
*/

Route::get('/register/{tipo}', [UsuarioController::class, 'formRegistroTipo'])
    ->whereIn('tipo', ['estudiante', 'empleado'])
    ->middleware('guest')
    ->name('register.tipo');

Route::post('/register', [UsuarioController::class, 'crearWeb'])
    ->middleware('guest')
    ->name('register.store');

/*
|--------------------------------------------------------------------------
| PANELES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth','roleid:2'])
    ->get('/panel-estudiante', fn() => view('estudiante'));

Route::middleware(['auth','roleid:4'])
    ->get('/panel-coordinador', fn() => view('panel.coordinador'));

Route::middleware(['auth','roleid:5'])
    ->get('/panel-secretario', fn() => view('panel.secretario'));

/*
|--------------------------------------------------------------------------
| OTRAS VISTAS
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return view('dashboard');


});

Route::get('/cambio-carrera', function () {
    return view('cambio_carrera');
});

// Ruta módulo de seguridad
Route::get('/seguridad', [RolController::class, 'index'])->name('seguridad.index');
Route::post('/seguridad/rol', [RolController::class, 'storeRol'])->name('seguridad.rol.store');
Route::put('/seguridad/rol/{id}', [RolController::class, 'updateRol'])->name('seguridad.rol.update');
Route::post('/seguridad/asignar-permisos-objeto', [RolController::class, 'asignarPermisosObjeto'])->name('seguridad.asignar.objeto');
Route::delete('/seguridad/asignacion/{id}', [RolController::class, 'deleteAsignacion'])->name('seguridad.asignacion.delete');
