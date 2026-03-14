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

/*
|--------------------------------------------------------------------------
| API
|--------------------------------------------------------------------------
*/

Route::prefix('api')->group(function () {

    // BITACORA
    Route::get('bitacora/index', [BitacoraController::class, 'index']);
    Route::get('bitacora/ver/{fi}/{ff}', [BitacoraController::class, 'ver']);
    Route::get('bitacora/ingresar/{id_usuario}/{id_objeto}/{accion}/{fecha}/{desc}', [BitacoraController::class, 'ingresar']);

    // AUDITORIA
    Route::get('auditoria/ver/{fi}/{ff}', [AuditoriaController::class, 'ver']);
    Route::get('auditoria/ingresar/{id_usuario}/{id_objeto}/{accion}/{descripcion}/{fecha}', [AuditoriaController::class, 'ingresar']);

    // CANCELACIONES
    Route::post('cancelaciones/crear', [DocumentoExcepcionalController::class, 'subir']);
    Route::get('cancelaciones/todas', [DocumentoExcepcionalController::class, 'obtenerTodos']);

    // ... (puedes dejar el resto igual como lo tienes)
});

/*
|--------------------------------------------------------------------------
| WEB BASE
|--------------------------------------------------------------------------
*/

//Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/', fn() => redirect()->route('portal'))->name('root');

Route::get('/portal', fn() => view('auth.choose_portal'))
    ->middleware('guest')
    ->name('portal');


/*
|--------------------------------------------------------------------------
| VISTAS
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', fn() => view('dashboard'));

Route::get('/cancelacion-excepcional', fn() => view('cancelacion'))
    ->middleware('auth')
    ->name('cancelacion.index');

Route::get('/cambio-carrera', fn() => view('cambio_carrera'));

/*
|--------------------------------------------------------------------------
| MODULOS
|--------------------------------------------------------------------------
*/

require __DIR__.'/Modulos/login.php';
require __DIR__.'/Modulos/seguridad.php';
require __DIR__.'/Modulos/usuarios.php'; // 👈 NUEVO
