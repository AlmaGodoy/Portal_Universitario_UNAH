<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// ✅ Sin roleid:2 — el LoginController ya garantiza que solo estudiantes llegan aquí
Route::middleware(['auth', 'session.timeout'])->get('/dashboard', function () {

    $user = Auth::user();
    $displayName = 'Alumno';

    if ($user) {
        if (isset($user->persona) && !empty($user->persona->nombre_persona)) {
            $displayName = trim($user->persona->nombre_persona);
        } elseif (!empty($user->nombre_persona)) {
            $displayName = trim($user->nombre_persona);
        } elseif (!empty($user->name)) {
            $displayName = trim($user->name);
        } elseif (!empty($user->id_persona)) {
            $persona = DB::table('tbl_persona')
                ->where('id_persona', $user->id_persona)
                ->first();

            if ($persona && !empty($persona->nombre_persona)) {
                $displayName = trim($persona->nombre_persona);
            }
        } elseif (!empty($user->email)) {
            $displayName = trim($user->email);
        }
    }

    $parts = preg_split('/\s+/', trim($displayName));
    $initials = '';

    foreach (array_slice($parts, 0, 2) as $part) {
        if (!empty($part)) {
            $initials .= strtoupper(mb_substr($part, 0, 1));
        }
    }

    if ($initials === '') {
        $initials = 'A';
    }

    return view('dashboard', compact('displayName', 'initials'));

})->name('dashboard');

Route::middleware('auth')->post('/session/keep-alive', function (Request $request) {
    $request->session()->put('last_activity_time', time());

    return response()->json([
        'ok' => true,
        'message' => 'Sesión renovada correctamente.'
    ]);
})->name('session.keepalive');

Route::middleware('auth')->post('/session/logout-inactive', function (Request $request) {
    $loginTipo = session('login_tipo');

    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    $redirectUrl = '/portal';

    if ($loginTipo === 'estudiante') {
        $redirectUrl = route('login.tipo', ['tipo' => 'estudiante']);
    } elseif ($loginTipo === 'empleado') {
        $redirectUrl = route('login.tipo', ['tipo' => 'empleado']);
    }

    return response()->json([
        'ok' => true,
        'redirect' => $redirectUrl,
        'message' => 'La sesión expiró por inactividad.'
    ]);
})->name('session.logout.inactive');