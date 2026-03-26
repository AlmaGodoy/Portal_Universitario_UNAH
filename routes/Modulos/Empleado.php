<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\EmpleadoController;

/*
|--------------------------------------------------------------------------
| MODULO: EMPLEADOS
|--------------------------------------------------------------------------
|
| Este módulo trabaja con sesión/auth y vistas Blade.
| Se deja una ruta principal que delega al controller para decidir
| a qué panel enviar al usuario según su tipo_empleado.
|
*/

/*
|--------------------------------------------------------------------------
| MODULO: EMPLEADOS (API / ACCESO PRINCIPAL)
|--------------------------------------------------------------------------
*/
Route::prefix('api/empleados')->middleware(['auth'])->group(function () {
    Route::get('panel', [EmpleadoController::class, 'index'])->name('empleados.api.panel');
});


/*
|--------------------------------------------------------------------------
| FRONTEND - EMPLEADOS (RUTA PRINCIPAL)
|--------------------------------------------------------------------------
|
| Esta ruta manda al usuario autenticado al panel correcto:
| - secretaria_carrera / secretaria_academica -> empleados.gestion_secretaria
| - coordinador                              -> empleados.panel_coordinador
| - administrador                            -> empleados.administracion_sistema
|
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/empleados', [EmpleadoController::class, 'index'])
        ->name('empleados.index');

    Route::get('/empleados/panel', [EmpleadoController::class, 'index'])
        ->name('empleados.panel');
});


/*
|--------------------------------------------------------------------------
| FRONTEND - SECRETARÍAS
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/empleados/secretaria', function () {
        $user = Auth::user()?->load('empleado');

        if (!$user || !$user->empleado || $user->empleado->estado !== 'activo') {
            return redirect()->route('portal')->with('error', 'Acceso denegado o cuenta inactiva.');
        }

        if (!in_array($user->empleado->tipo_empleado, ['secretaria_carrera', 'secretaria_academica'])) {
            return redirect()->route('portal')->with('error', 'Rol no autorizado.');
        }

        $tipo = $user->empleado->tipo_empleado;

        $datos = [
            'titulo'  => ($tipo === 'secretaria_carrera') ? 'Gestión de Carrera' : 'Gestión Académica',
            'usuario' => $user->id_persona,
            'rol'     => $tipo,
        ];

        return view('empleados.gestion_secretaria', compact('datos'));
    })->name('empleados.secretaria');
});


/*
|--------------------------------------------------------------------------
| FRONTEND - COORDINADOR
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/empleados/coordinador', function () {
        $user = Auth::user()?->load('empleado');

        if (!$user || !$user->empleado || $user->empleado->estado !== 'activo') {
            return redirect()->route('portal')->with('error', 'Acceso denegado o cuenta inactiva.');
        }

        if ($user->empleado->tipo_empleado !== 'coordinador') {
            return redirect()->route('portal')->with('error', 'Rol no autorizado.');
        }

        $datos = [
            'titulo'  => 'Panel de Control: Coordinación',
            'usuario' => $user->id_persona,
        ];

        return view('empleados.panel_coordinador', compact('datos'));
    })->name('empleados.coordinador');
});


/*
|--------------------------------------------------------------------------
| FRONTEND - ADMINISTRADOR
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/empleados/administrador', function () {
        $user = Auth::user()?->load('empleado');

        if (!$user || !$user->empleado || $user->empleado->estado !== 'activo') {
            return redirect()->route('portal')->with('error', 'Acceso denegado o cuenta inactiva.');
        }

        if ($user->empleado->tipo_empleado !== 'administrador') {
            return redirect()->route('portal')->with('error', 'Rol no autorizado.');
        }

        $datos = [
            'titulo'  => 'Mantenimiento del Sistema',
            'usuario' => $user->id_persona,
        ];

        return view('empleados.administracion_sistema', compact('datos'));
    })->name('empleados.administrador');
});
