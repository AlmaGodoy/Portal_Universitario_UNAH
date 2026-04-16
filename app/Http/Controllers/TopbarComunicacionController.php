<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TopbarComunicacionController extends Controller
{
    /**
     * Obtiene el id_usuario real del usuario autenticado.
     */
    private function getIdUsuarioAutenticado()
    {
        $user = Auth::user();

        return $user->id_usuario ?? $user->id ?? null;
    }

    /**
     * Resumen general para la topbar:
     * - conteo de notificaciones no leídas
     * - últimas notificaciones
     * - conteo de mensajes no leídos
     * - últimos mensajes
     */
    public function resumen()
    {
        $idUsuario = $this->getIdUsuarioAutenticado();

        if (!$idUsuario) {
            return response()->json([
                'ok' => false,
                'message' => 'No se pudo identificar el usuario autenticado.'
            ], 401);
        }

        $notificaciones = DB::table('tbl_notificacion')
            ->where('id_usuario_destino', $idUsuario)
            ->orderByDesc('fecha_creacion')
            ->limit(8)
            ->get([
                'id_notificacion',
                'titulo',
                'mensaje',
                'tipo',
                'url_destino',
                'leida',
                'fecha_creacion'
            ]);

        $conteoNotificaciones = DB::table('tbl_notificacion')
            ->where('id_usuario_destino', $idUsuario)
            ->where('leida', 0)
            ->count();

        $mensajes = DB::table('tbl_mensaje as m')
            ->leftJoin('tbl_usuario as u', 'u.id_usuario', '=', 'm.id_usuario_remitente')
            ->where('m.id_usuario_destino', $idUsuario)
            ->orderByDesc('m.fecha_creacion')
            ->limit(8)
            ->get([
                'm.id_mensaje',
                'm.asunto',
                'm.mensaje',
                'm.leido',
                'm.fecha_creacion',
                DB::raw("CONCAT('Usuario #', COALESCE(u.id_usuario, '')) as remitente_nombre")
            ]);

        $conteoMensajes = DB::table('tbl_mensaje')
            ->where('id_usuario_destino', $idUsuario)
            ->where('leido', 0)
            ->count();

        return response()->json([
            'ok' => true,
            'notificaciones' => [
                'count' => $conteoNotificaciones,
                'items' => $notificaciones,
            ],
            'mensajes' => [
                'count' => $conteoMensajes,
                'items' => $mensajes,
            ],
        ]);
    }

    /**
     * Marca todas las notificaciones como leídas.
     */
    public function marcarTodasNotificacionesLeidas()
    {
        $idUsuario = $this->getIdUsuarioAutenticado();

        if (!$idUsuario) {
            return response()->json([
                'ok' => false,
                'message' => 'No se pudo identificar el usuario autenticado.'
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
            'message' => 'Todas las notificaciones fueron marcadas como leídas.'
        ]);
    }

    /**
     * Marca una notificación individual como leída.
     */
    public function marcarNotificacionLeida($id)
    {
        $idUsuario = $this->getIdUsuarioAutenticado();

        if (!$idUsuario) {
            return response()->json([
                'ok' => false,
                'message' => 'No se pudo identificar el usuario autenticado.'
            ], 401);
        }

        $actualizado = DB::table('tbl_notificacion')
            ->where('id_notificacion', $id)
            ->where('id_usuario_destino', $idUsuario)
            ->update([
                'leida' => 1,
                'fecha_leida' => now(),
            ]);

        return response()->json([
            'ok' => $actualizado > 0,
            'message' => $actualizado > 0
                ? 'Notificación marcada como leída.'
                : 'No se pudo marcar la notificación.'
        ]);
    }

    /**
     * Marca un mensaje individual como leído.
     */
    public function marcarMensajeLeido($id)
    {
        $idUsuario = $this->getIdUsuarioAutenticado();

        if (!$idUsuario) {
            return response()->json([
                'ok' => false,
                'message' => 'No se pudo identificar el usuario autenticado.'
            ], 401);
        }

        $actualizado = DB::table('tbl_mensaje')
            ->where('id_mensaje', $id)
            ->where('id_usuario_destino', $idUsuario)
            ->update([
                'leido' => 1,
                'fecha_leido' => now(),
            ]);

        return response()->json([
            'ok' => $actualizado > 0,
            'message' => $actualizado > 0
                ? 'Mensaje marcado como leído.'
                : 'No se pudo marcar el mensaje.'
        ]);
    }

    /**
     * Crear una notificación.
     */
    public function crearNotificacion(Request $request)
    {
        $request->validate([
            'id_usuario_destino' => 'required|integer',
            'titulo' => 'required|string|max:150',
            'mensaje' => 'required|string|max:255',
            'tipo' => 'nullable|string|max:30',
            'url_destino' => 'nullable|string|max:255',
        ]);

        $id = DB::table('tbl_notificacion')->insertGetId([
            'id_usuario_destino' => $request->id_usuario_destino,
            'titulo' => $request->titulo,
            'mensaje' => $request->mensaje,
            'tipo' => $request->tipo ?? 'info',
            'url_destino' => $request->url_destino,
            'leida' => 0,
            'fecha_creacion' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Notificación creada correctamente.',
            'id_notificacion' => $id
        ]);
    }

    /**
     * Crear un mensaje.
     */
    public function crearMensaje(Request $request)
    {
        $idUsuarioRemitente = $this->getIdUsuarioAutenticado();

        if (!$idUsuarioRemitente) {
            return response()->json([
                'ok' => false,
                'message' => 'No se pudo identificar el usuario autenticado.'
            ], 401);
        }

        $request->validate([
            'id_usuario_destino' => 'required|integer',
            'asunto' => 'nullable|string|max:150',
            'mensaje' => 'required|string',
        ]);

        $id = DB::table('tbl_mensaje')->insertGetId([
            'id_usuario_remitente' => $idUsuarioRemitente,
            'id_usuario_destino' => $request->id_usuario_destino,
            'asunto' => $request->asunto,
            'mensaje' => $request->mensaje,
            'leido' => 0,
            'fecha_creacion' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Mensaje creado correctamente.',
            'id_mensaje' => $id
        ]);
    }
}
