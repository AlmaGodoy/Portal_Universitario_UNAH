<?php

namespace App\Http\Controllers;

use App\Models\Mensaje;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MensajeController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            abort(403, 'No autorizado.');
        }

        $idUsuario = (int) $usuario->id_usuario;
        $buscar = trim((string) $request->get('buscar', ''));

        $mensajes = Mensaje::with(['remitente.persona', 'destinatario.persona'])
            ->where('id_destinatario', $idUsuario)
            ->where('eliminado_destinatario', false)
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($subquery) use ($buscar) {
                    $subquery->where('asunto', 'LIKE', "%{$buscar}%")
                        ->orWhere('contenido', 'LIKE', "%{$buscar}%");
                });
            })
            ->orderBy('fecha_creacion', 'desc')
            ->paginate(10)
            ->withQueryString();

        $noLeidos = Mensaje::where('id_destinatario', $idUsuario)
            ->where('eliminado_destinatario', false)
            ->where('leido', false)
            ->count();

        return view('mensajes.index', compact(
            'mensajes',
            'noLeidos',
            'buscar'
        ));
    }

    public function enviados(Request $request)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            abort(403, 'No autorizado.');
        }

        $idUsuario = (int) $usuario->id_usuario;
        $buscar = trim((string) $request->get('buscar', ''));

        $mensajes = Mensaje::with(['remitente.persona', 'destinatario.persona'])
            ->where('id_remitente', $idUsuario)
            ->where('eliminado_remitente', false)
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($subquery) use ($buscar) {
                    $subquery->where('asunto', 'LIKE', "%{$buscar}%")
                        ->orWhere('contenido', 'LIKE', "%{$buscar}%");
                });
            })
            ->orderBy('fecha_creacion', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('mensajes.enviados', compact(
            'mensajes',
            'buscar'
        ));
    }

    public function create()
    {
        $usuario = Auth::user();

        if (!$usuario) {
            abort(403, 'No autorizado.');
        }

        $idUsuario = (int) $usuario->id_usuario;
        $idRol = (int) ($usuario->id_rol ?? 0);

        $destinatarios = User::with('persona')
            ->where('id_usuario', '<>', $idUsuario)
            ->when($idRol === 2, function ($query) {
                $query->where('id_rol', '<>', 2);
            })
            ->orderBy('id_rol')
            ->orderBy('id_usuario')
            ->get();

        return view('mensajes.crear', compact('destinatarios'));
    }

    public function store(Request $request)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            abort(403, 'No autorizado.');
        }

        $idUsuario = (int) $usuario->id_usuario;
        $idRol = (int) ($usuario->id_rol ?? 0);

        $datos = $request->validate([
            'id_destinatario' => [
                'required',
                'integer',
                Rule::exists('tbl_usuario', 'id_usuario'),
                Rule::notIn([$idUsuario]),
            ],
            'asunto' => [
                'required',
                'string',
                'max:150',
            ],
            'contenido' => [
                'required',
                'string',
                'min:2',
                'max:5000',
            ],
        ], [
            'id_destinatario.required' => 'Debe seleccionar un destinatario.',
            'id_destinatario.exists' => 'El destinatario seleccionado no existe.',
            'id_destinatario.not_in' => 'No puede enviarse un mensaje a usted mismo.',
            'asunto.required' => 'Debe escribir el asunto del mensaje.',
            'asunto.max' => 'El asunto no puede superar los 150 caracteres.',
            'contenido.required' => 'Debe escribir el contenido del mensaje.',
            'contenido.min' => 'El mensaje debe contener al menos 2 caracteres.',
            'contenido.max' => 'El mensaje no puede superar los 5000 caracteres.',
        ]);

        $destinatario = User::where('id_usuario', $datos['id_destinatario'])
            ->firstOrFail();

        if ($idRol === 2 && (int) $destinatario->id_rol === 2) {
            return back()
                ->withInput()
                ->withErrors([
                    'id_destinatario' => 'El estudiante solamente puede enviar mensajes a empleados.',
                ]);
        }

        Mensaje::create([
            'id_remitente' => $idUsuario,
            'id_destinatario' => (int) $datos['id_destinatario'],
            'id_mensaje_padre' => null,
            'asunto' => trim($datos['asunto']),
            'contenido' => trim($datos['contenido']),
            'leido' => false,
            'fecha_lectura' => null,
            'eliminado_remitente' => false,
            'eliminado_destinatario' => false,
        ]);

        return redirect()
            ->route('mensajes.enviados')
            ->with('success', 'El mensaje fue enviado correctamente.');
    }

    public function show(int $idMensaje)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            abort(403, 'No autorizado.');
        }

        $idUsuario = (int) $usuario->id_usuario;

        $mensaje = $this->buscarMensajeAutorizado($idMensaje, $idUsuario);

        $idMensajePrincipal = $mensaje->id_mensaje_padre
            ? (int) $mensaje->id_mensaje_padre
            : (int) $mensaje->id_mensaje;

        $mensajePrincipal = Mensaje::where('id_mensaje', $idMensajePrincipal)
            ->where(function ($query) use ($idUsuario) {
                $query->where('id_remitente', $idUsuario)
                    ->orWhere('id_destinatario', $idUsuario);
            })
            ->firstOrFail();

        Mensaje::where('id_destinatario', $idUsuario)
            ->where('leido', false)
            ->where(function ($query) use ($idMensajePrincipal) {
                $query->where('id_mensaje', $idMensajePrincipal)
                    ->orWhere('id_mensaje_padre', $idMensajePrincipal);
            })
            ->update([
                'leido' => true,
                'fecha_lectura' => now(),
            ]);

        $conversacion = Mensaje::with(['remitente.persona', 'destinatario.persona'])
            ->where(function ($query) use ($idMensajePrincipal) {
                $query->where('id_mensaje', $idMensajePrincipal)
                    ->orWhere('id_mensaje_padre', $idMensajePrincipal);
            })
            ->where(function ($query) use ($idUsuario) {
                $query->where(function ($subquery) use ($idUsuario) {
                    $subquery->where('id_remitente', $idUsuario)
                        ->where('eliminado_remitente', false);
                })
                ->orWhere(function ($subquery) use ($idUsuario) {
                    $subquery->where('id_destinatario', $idUsuario)
                        ->where('eliminado_destinatario', false);
                });
            })
            ->orderBy('fecha_creacion', 'asc')
            ->get();

        return view('mensajes.ver', compact(
            'mensajePrincipal',
            'conversacion'
        ));
    }

    public function responder(Request $request, int $idMensaje)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            abort(403, 'No autorizado.');
        }

        $idUsuario = (int) $usuario->id_usuario;

        $mensaje = $this->buscarMensajeAutorizado($idMensaje, $idUsuario);

        $datos = $request->validate([
            'contenido' => [
                'required',
                'string',
                'min:2',
                'max:5000',
            ],
        ], [
            'contenido.required' => 'Debe escribir la respuesta.',
            'contenido.min' => 'La respuesta debe contener al menos 2 caracteres.',
            'contenido.max' => 'La respuesta no puede superar los 5000 caracteres.',
        ]);

        $idDestinatario = (int) $mensaje->id_remitente === $idUsuario
            ? (int) $mensaje->id_destinatario
            : (int) $mensaje->id_remitente;

        $idMensajePrincipal = $mensaje->id_mensaje_padre
            ? (int) $mensaje->id_mensaje_padre
            : (int) $mensaje->id_mensaje;

        $asunto = preg_match('/^RE:/i', $mensaje->asunto)
            ? $mensaje->asunto
            : 'RE: ' . $mensaje->asunto;

        $respuesta = Mensaje::create([
            'id_remitente' => $idUsuario,
            'id_destinatario' => $idDestinatario,
            'id_mensaje_padre' => $idMensajePrincipal,
            'asunto' => Str::limit($asunto, 150, ''),
            'contenido' => trim($datos['contenido']),
            'leido' => false,
            'fecha_lectura' => null,
            'eliminado_remitente' => false,
            'eliminado_destinatario' => false,
        ]);

        return redirect()
            ->route('mensajes.show', $respuesta->id_mensaje)
            ->with('success', 'La respuesta fue enviada correctamente.');
    }

    public function destroy(int $idMensaje)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            abort(403, 'No autorizado.');
        }

        $idUsuario = (int) $usuario->id_usuario;

        $mensaje = $this->buscarMensajeAutorizado($idMensaje, $idUsuario);

        if ((int) $mensaje->id_remitente === $idUsuario) {
            $mensaje->eliminado_remitente = true;
        }

        if ((int) $mensaje->id_destinatario === $idUsuario) {
            $mensaje->eliminado_destinatario = true;
        }

        $mensaje->save();

        return back()
            ->with('success', 'El mensaje fue eliminado de su bandeja.');
    }

    public function recientes()
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return response()->json([
                'ok' => false,
                'message' => 'No autorizado.',
            ], 401);
        }

        $idUsuario = (int) $usuario->id_usuario;

        $mensajes = Mensaje::with('remitente.persona')
            ->where('id_destinatario', $idUsuario)
            ->where('eliminado_destinatario', false)
            ->orderBy('fecha_creacion', 'desc')
            ->limit(5)
            ->get()
            ->map(function (Mensaje $mensaje) {
                $nombre = optional(optional($mensaje->remitente)->persona)->nombre_persona
                    ?? optional($mensaje->remitente)->name
                    ?? optional($mensaje->remitente)->email
                    ?? 'Usuario';

                return [
                    'id_mensaje' => $mensaje->id_mensaje,
                    'remitente' => $nombre,
                    'iniciales' => $this->obtenerIniciales($nombre),
                    'asunto' => $mensaje->asunto,
                    'contenido' => Str::limit(strip_tags($mensaje->contenido), 70),
                    'leido' => (bool) $mensaje->leido,
                    'tiempo' => optional($mensaje->fecha_creacion)->diffForHumans(),
                    'url' => route('mensajes.show', $mensaje->id_mensaje),
                ];
            });

        $noLeidos = Mensaje::where('id_destinatario', $idUsuario)
            ->where('eliminado_destinatario', false)
            ->where('leido', false)
            ->count();

        return response()->json([
            'ok' => true,
            'unread_count' => $noLeidos,
            'mensajes' => $mensajes,
        ]);
    }

    private function buscarMensajeAutorizado(int $idMensaje, int $idUsuario): Mensaje
    {
        return Mensaje::where('id_mensaje', $idMensaje)
            ->where(function ($query) use ($idUsuario) {
                $query->where(function ($subquery) use ($idUsuario) {
                    $subquery->where('id_remitente', $idUsuario)
                        ->where('eliminado_remitente', false);
                })
                ->orWhere(function ($subquery) use ($idUsuario) {
                    $subquery->where('id_destinatario', $idUsuario)
                        ->where('eliminado_destinatario', false);
                });
            })
            ->firstOrFail();
    }

    private function obtenerIniciales(string $nombre): string
    {
        $partes = preg_split('/\s+/', trim($nombre), -1, PREG_SPLIT_NO_EMPTY);

        $iniciales = '';

        foreach (array_slice($partes ?: [], 0, 2) as $parte) {
            $iniciales .= mb_strtoupper(mb_substr($parte, 0, 1));
        }

        return $iniciales !== '' ? $iniciales : 'U';
    }
}