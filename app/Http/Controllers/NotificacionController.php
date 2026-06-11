<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificacionController extends Controller
{
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

    public function recientes()
    {
        $idUsuario = $this->obtenerIdUsuarioAutenticado();

        if (!$idUsuario) {
            return response()->json([
                'ok' => false,
                'message' => 'Usuario no autenticado.',
                'id_usuario_detectado' => null,
                'unread_count' => 0,
                'notificaciones' => [],
            ], 401);
        }

        $notificaciones = DB::table('tbl_notificacion')
            ->where('id_usuario_destino', $idUsuario)
            ->orderByDesc('id_notificacion')
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
                    'fecha_creacion' => $notificacion->fecha_creacion,
                    'tiempo' => $this->formatearTiempo($notificacion->fecha_creacion),
                    'icono' => $this->iconoPorTipo($notificacion->tipo),
                    'color' => $this->colorPorTipo($notificacion->tipo),
                ];
            });

        $noLeidas = DB::table('tbl_notificacion')
            ->where('id_usuario_destino', $idUsuario)
            ->where('leida', 0)
            ->count();

        return response()->json([
            'ok' => true,
            'id_usuario_detectado' => $idUsuario,
            'unread_count' => $noLeidas,
            'notificaciones' => $notificaciones,
        ]);
    }

    public function index()
    {
        $idUsuario = $this->obtenerIdUsuarioAutenticado();

        if (!$idUsuario) {
            abort(401, 'Usuario no autenticado.');
        }

        $notificaciones = DB::table('tbl_notificacion')
            ->where('id_usuario_destino', $idUsuario)
            ->orderByDesc('id_notificacion')
            ->paginate(10);

        $notificaciones->getCollection()->transform(function ($notificacion) {
            $notificacion->icono = $this->iconoPorTipo($notificacion->tipo);
            $notificacion->color = $this->colorPorTipo($notificacion->tipo);
            return $notificacion;
        });

        return view('notificaciones', compact('notificaciones'));
    }

    public function marcarLeida($id_notificacion)
    {
        $idUsuario = $this->obtenerIdUsuarioAutenticado();

        if (!$idUsuario) {
            return response()->json([
                'ok' => false,
                'message' => 'Usuario no autenticado.',
            ], 401);
        }

        $existe = DB::table('tbl_notificacion')
            ->where('id_notificacion', $id_notificacion)
            ->where('id_usuario_destino', $idUsuario)
            ->exists();

        if (!$existe) {
            return response()->json([
                'ok' => false,
                'message' => 'Notificación no encontrada.',
            ], 404);
        }

        DB::table('tbl_notificacion')
            ->where('id_notificacion', $id_notificacion)
            ->where('id_usuario_destino', $idUsuario)
            ->update([
                'leida' => 1,
                'fecha_leida' => now(),
            ]);

        return response()->json([
            'ok' => true,
            'message' => 'Notificación marcada como leída.',
        ]);
    }

    public function marcarTodasLeidas()
    {
        $idUsuario = $this->obtenerIdUsuarioAutenticado();

        if (!$idUsuario) {
            return response()->json([
                'ok' => false,
                'message' => 'Usuario no autenticado.',
            ], 401);
        }

        DB::table('tbl_notificacion')
            ->where('id_usuario_destino', $idUsuario)
            ->where('leida', 0)
            ->update([
                'leida' => 1,
                'fecha_leida' => now(),
            ]);

        return response()->json([
            'ok' => true,
            'message' => 'Todas las notificaciones fueron marcadas como leídas.',
        ]);
    }

    public function abrir($id_notificacion)
    {
        $idUsuario = $this->obtenerIdUsuarioAutenticado();

        if (!$idUsuario) {
            abort(401, 'Usuario no autenticado.');
        }

        $notificacion = DB::table('tbl_notificacion')
            ->where('id_notificacion', $id_notificacion)
            ->where('id_usuario_destino', $idUsuario)
            ->first();

        if (!$notificacion) {
            abort(404, 'Notificación no encontrada.');
        }

        DB::table('tbl_notificacion')
            ->where('id_notificacion', $id_notificacion)
            ->where('id_usuario_destino', $idUsuario)
            ->update([
                'leida' => 1,
                'fecha_leida' => now(),
            ]);

        return redirect($notificacion->url_destino ?: '/dashboard');
    }

    private function iconoPorTipo($tipo)
    {
        return match ($tipo) {
            'success' => 'fas fa-circle-check',
            'warning' => 'fas fa-triangle-exclamation',
            'danger', 'error' => 'fas fa-circle-xmark',
            default => 'fas fa-file-alt',
        };
    }

    private function colorPorTipo($tipo)
    {
        return match ($tipo) {
            'success' => 'green',
            'warning' => 'gold',
            'danger', 'error' => 'red',
            default => 'blue',
        };
    }

    private function formatearTiempo($fecha)
    {
        if (!$fecha) {
            return '';
        }

        $fechaCarbon = Carbon::parse($fecha);
        $diff = now()->diffInSeconds($fechaCarbon);

        if ($diff < 60) {
            return 'Hace unos segundos';
        }

        if ($diff < 3600) {
            return 'Hace ' . floor($diff / 60) . ' min';
        }

        if ($diff < 86400) {
            $horas = floor($diff / 3600);
            return 'Hace ' . $horas . ' hora' . ($horas > 1 ? 's' : '');
        }

        if ($diff < 172800) {
            return 'Ayer';
        }

        return 'Hace ' . floor($diff / 86400) . ' días';
    }
}