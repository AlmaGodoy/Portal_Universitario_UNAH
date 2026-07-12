<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuditoriaController extends Controller
{
    public function index()
    {
        return $this->redirectAuditoria();
    }

    public function redirectAuditoria()
    {
        $usuario = Auth::user();

        if (!$usuario) {
            abort(403, 'No autorizado.');
        }

        return match ((int) $usuario->id_rol) {
            1 => redirect()->route('auditoria.administrativa'),
            4 => redirect()->route('auditoria.coordinador'),
            5 => redirect()->route('auditoria.general'),
            default => abort(
                403,
                'No tiene autorización para consultar la auditoría.'
            ),
        };
    }

    /**
     * Rol 1: acceso global.
     */
    public function administrativa(Request $request)
    {
        $usuario = $this->usuarioAutorizado([1]);
        $filtros = $this->validarFiltros($request);

        $auditorias = $this->consultarAuditorias(
            request: $request,
            filtros: $filtros
        );

        $registros = $auditorias;

        $carreras = DB::table('tbl_carrera')
            ->select('id_carrera', 'nombre_carrera')
            ->orderBy('nombre_carrera')
            ->get();

        $fecha_inicio = $filtros['fecha_inicio'];
        $fecha_fin = $filtros['fecha_fin'];
        $id_rol = (int) $usuario->id_rol;

        return view(
            'auditoria_secretaria_academica',
            compact(
                'auditorias',
                'registros',
                'carreras',
                'filtros',
                'fecha_inicio',
                'fecha_fin',
                'id_rol'
            )
        );
    }

    /**
     * Rol 4: coordinador, limitado a su carrera.
     */
    public function coordinador(Request $request)
    {
        $usuario = $this->usuarioAutorizado([4]);

        $idCarrera = $this->obtenerCarreraEmpleado(
            $usuario->id_persona
        );

        if (!$idCarrera) {
            return back()->with(
                'error',
                'El coordinador no tiene una carrera asignada.'
            );
        }

        $filtros = $this->validarFiltros($request);

        $auditorias = $this->consultarAuditorias(
            request: $request,
            filtros: $filtros,
            carreraObligatoria: $idCarrera
        );

        $registros = $auditorias;
        $carreras = collect();

        $fecha_inicio = $filtros['fecha_inicio'];
        $fecha_fin = $filtros['fecha_fin'];
        $id_rol = (int) $usuario->id_rol;

        return view(
            'auditoria_coordinador',
            compact(
                'auditorias',
                'registros',
                'carreras',
                'filtros',
                'fecha_inicio',
                'fecha_fin',
                'id_rol'
            )
        );
    }

    /**
     * Rol 5: Secretaría de Carrera, limitada a su carrera.
     */
    public function general(Request $request)
    {
        $usuario = $this->usuarioAutorizado([5]);

        $idCarrera = $this->obtenerCarreraEmpleado(
            $usuario->id_persona
        );

        if (!$idCarrera) {
            return back()->with(
                'error',
                'La Secretaría de Carrera no tiene una carrera asignada.'
            );
        }

        $filtros = $this->validarFiltros($request);

        $auditorias = $this->consultarAuditorias(
            request: $request,
            filtros: $filtros,
            carreraObligatoria: $idCarrera
        );

        $registros = $auditorias;
        $carreras = collect();

        $fecha_inicio = $filtros['fecha_inicio'];
        $fecha_fin = $filtros['fecha_fin'];
        $id_rol = (int) $usuario->id_rol;

        return view(
            'auditoria_secretaria_general',
            compact(
                'auditorias',
                'registros',
                'carreras',
                'filtros',
                'fecha_inicio',
                'fecha_fin',
                'id_rol'
            )
        );
    }

    private function consultarAuditorias(
        Request $request,
        array $filtros,
        ?int $carreraObligatoria = null
    ) {
        $consulta = DB::table('tbl_auditoria as a')
            ->select('a.*')
            ->whereDate('a.fecha', '>=', $filtros['fecha_inicio'])
            ->whereDate('a.fecha', '<=', $filtros['fecha_fin']);

        if ($carreraObligatoria) {
            $consulta->where(function ($query) use ($carreraObligatoria) {
                $query
                    ->where('a.id_carrera', $carreraObligatoria)
                    ->orWhere(
                        'a.id_carrera_actor',
                        $carreraObligatoria
                    );
            });
        } elseif ($filtros['id_carrera']) {
            $consulta->where(function ($query) use ($filtros) {
                $query
                    ->where('a.id_carrera', $filtros['id_carrera'])
                    ->orWhere(
                        'a.id_carrera_actor',
                        $filtros['id_carrera']
                    );
            });
        }

        if ($filtros['id_usuario']) {
            $consulta->where(
                'a.id_usuario',
                $filtros['id_usuario']
            );
        }

        if ($filtros['id_rol']) {
            $consulta->where('a.id_rol', $filtros['id_rol']);
        }

        if ($filtros['id_objeto']) {
            $consulta->where(
                'a.id_objeto',
                $filtros['id_objeto']
            );
        }

        if ($filtros['id_tramite']) {
            $consulta->where(
                'a.id_tramite',
                $filtros['id_tramite']
            );
        }

        if ($filtros['accion']) {
            $consulta->where(
                'a.accion',
                'like',
                '%' . $filtros['accion'] . '%'
            );
        }

        if ($filtros['operacion']) {
            $consulta->where(
                'a.operacion',
                $filtros['operacion']
            );
        }

        if ($filtros['tabla_afectada']) {
            $consulta->where(
                'a.tabla_afectada',
                'like',
                '%' . $filtros['tabla_afectada'] . '%'
            );
        }

        if ($filtros['nivel']) {
            $consulta->where('a.nivel', $filtros['nivel']);
        }

        $perPage = (int) $request->input('per_page', 10);
        $perPage = min(max($perPage, 10), 100);

        return $consulta
            ->orderByDesc('a.fecha')
            ->orderByDesc('a.id_auditoria')
            ->paginate($perPage)
            ->withQueryString();
    }

    private function validarFiltros(Request $request): array
    {
        $request->merge([
            'fecha_inicio' => $request->input(
                'fecha_inicio',
                now()->subMonth()->toDateString()
            ),
            'fecha_fin' => $request->input(
                'fecha_fin',
                now()->toDateString()
            ),
        ]);

        $datos = $request->validate([
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => [
                'required',
                'date',
                'after_or_equal:fecha_inicio',
            ],

            'id_usuario' => ['nullable', 'integer', 'min:1'],
            'id_rol' => ['nullable', 'integer', 'min:1'],
            'id_carrera' => ['nullable', 'integer', 'min:1'],
            'id_objeto' => ['nullable', 'integer', 'min:1'],
            'id_tramite' => ['nullable', 'integer', 'min:1'],

            'accion' => ['nullable', 'string', 'max:100'],
            'tabla_afectada' => [
                'nullable',
                'string',
                'max:100',
            ],

            'operacion' => [
                'nullable',
                'in:INSERT,UPDATE,DELETE,LOGIN,LOGOUT,OTRA',
            ],

            'nivel' => [
                'nullable',
                'in:INFO,ADVERTENCIA,ERROR,SEGURIDAD',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:10',
                'max:100',
            ],
        ]);

        return [
            'fecha_inicio' => $datos['fecha_inicio'],
            'fecha_fin' => $datos['fecha_fin'],
            'id_usuario' => $datos['id_usuario'] ?? null,
            'id_rol' => $datos['id_rol'] ?? null,
            'id_carrera' => $datos['id_carrera'] ?? null,
            'id_objeto' => $datos['id_objeto'] ?? null,
            'id_tramite' => $datos['id_tramite'] ?? null,
            'accion' => $datos['accion'] ?? null,
            'tabla_afectada' =>
                $datos['tabla_afectada'] ?? null,
            'operacion' => $datos['operacion'] ?? null,
            'nivel' => $datos['nivel'] ?? null,
        ];
    }

    private function obtenerCarreraEmpleado(
        ?int $idPersona
    ): ?int {
        if (!$idPersona) {
            return null;
        }

        $idCarrera = DB::table('tbl_empleados')
            ->where('id_persona', $idPersona)
            ->value('id_carrera');

        return $idCarrera ? (int) $idCarrera : null;
    }

    private function usuarioAutorizado(
        array $rolesPermitidos
    ) {
        $usuario = Auth::user();

        if (!$usuario) {
            abort(403, 'No autorizado.');
        }

        if (
            !in_array(
                (int) $usuario->id_rol,
                $rolesPermitidos,
                true
            )
        ) {
            abort(
                403,
                'No tiene autorización para consultar la auditoría.'
            );
        }

        return $usuario;
    }
}