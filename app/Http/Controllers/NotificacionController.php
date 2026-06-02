<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    /**
     * Obtener el id del usuario autenticado.
     */
    private function obtenerIdUsuarioAutenticado()
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        return $user->id_usuario
            ?? $user->id
            ?? $user->id_login
            ?? $user->id_usuario_login
            ?? null;
    }

    /**
     * Devuelve las últimas notificaciones para la campanita.
     */
    public function recientes()
    {
        $idUsuario = $this->obtenerIdUsuarioAutenticado();

        if (!$idUsuario) {
            return response()->json([
                'ok' => false,
                'message' => 'Usuario no autenticado.',
                'unread_count' => 0,
                'notificaciones' => [],
            ], 401);
        }

        $notificaciones = Notificacion::paraUsuario($idUsuario)
            ->recientes()
            ->limit(5)
            ->get()
            ->map(function ($notificacion) {
                return [
                    'id_notificacion' => $notificacion->id_notificacion,
                    'titulo' => $notificacion->titulo,
                    'mensaje' => $notificacion->mensaje,
                    'tipo' => $notificacion->tipo,
                    'url_destino' => $notificacion->url_destino,
                    'leida' => (bool) $notificacion->leida,
                    'fecha_creacion' => optional($notificacion->fecha_creacion)->format('Y-m-d H:i:s'),
                    'tiempo' => $this->formatearTiempo($notificacion->fecha_creacion),
                    'icono' => $notificacion->icono,
                    'color' => $notificacion->color,
                ];
            });

        $noLeidas = Notificacion::paraUsuario($idUsuario)
            ->noLeidas()
            ->count();

        return response()->json([
            'ok' => true,
            'id_usuario_detectado' => $idUsuario,
            'unread_count' => $noLeidas,
            'notificaciones' => $notificaciones,
        ]);
    }

    /**
     * Vista completa de notificaciones.
     */
    public function index()
    {
        $idUsuario = $this->obtenerIdUsuarioAutenticado();

        if (!$idUsuario) {
            abort(401, 'Usuario no autenticado.');
        }

        $notificaciones = Notificacion::paraUsuario($idUsuario)
            ->recientes()
            ->paginate(10);

        return view('notificaciones.index', compact('notificaciones'));
    }

    /**
     * Marcar una notificación como leída.
     */
    public function marcarLeida($idNotificacion)
    {
        $idUsuario = $this->obtenerIdUsuarioAutenticado();

        if (!$idUsuario) {
            return response()->json([
                'ok' => false,
                'message' => 'Usuario no autenticado.',
            ], 401);
        }

        $notificacion = Notificacion::paraUsuario($idUsuario)
            ->where('id_notificacion', $idNotificacion)
            ->first();

        if (!$notificacion) {
            return response()->json([
                'ok' => false,
                'message' => 'Notificación no encontrada.',
            ], 404);
        }

        $notificacion->marcarComoLeida();

        return response()->json([
            'ok' => true,
            'message' => 'Notificación marcada como leída.',
        ]);
    }

    /**
     * Marcar todas como leídas.
     */
    public function marcarTodasLeidas()
    {
        $idUsuario = $this->obtenerIdUsuarioAutenticado();

        if (!$idUsuario) {
            return response()->json([
                'ok' => false,
                'message' => 'Usuario no autenticado.',
            ], 401);
        }

        Notificacion::paraUsuario($idUsuario)
            ->noLeidas()
            ->update([
                'leida' => 1,
                'fecha_leida' => now(),
            ]);

        return response()->json([
            'ok' => true,
            'message' => 'Todas las notificaciones fueron marcadas como leídas.',
        ]);
    }

    /**
     * Abrir una notificación, marcarla como leída y redirigir.
     */
    public function abrir($idNotificacion)
    {
        $idUsuario = $this->obtenerIdUsuarioAutenticado();

        if (!$idUsuario) {
            abort(401, 'Usuario no autenticado.');
        }

        $notificacion = Notificacion::paraUsuario($idUsuario)
            ->where('id_notificacion', $idNotificacion)
            ->firstOrFail();

        $notificacion->marcarComoLeida();

        return redirect($notificacion->url_destino ?: '/dashboard');
    }

    /**
     * Formatear fecha para mostrar en la campanita.
     */
    private function formatearTiempo($fecha)
    {
        if (!$fecha) {
            return '';
        }

        $diff = now()->diffInSeconds($fecha);

        if ($diff < 60) {
            return 'Hace unos segundos';
        }

        if ($diff < 3600) {
            $minutos = floor($diff / 60);
            return 'Hace ' . $minutos . ' min';
        }

        if ($diff < 86400) {
            $horas = floor($diff / 3600);
            return 'Hace ' . $horas . ' hora' . ($horas > 1 ? 's' : '');
        }

        if ($diff < 172800) {
            return 'Ayer';
        }

        $dias = floor($diff / 86400);
        return 'Hace ' . $dias . ' días';
    }
}