<?php

namespace App\Http\Controllers;

use App\Models\Graficas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmpleadoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Identificadores de roles
    |--------------------------------------------------------------------------
    |
    | 1 = Secretaría General
    | 3 = Secretaría Académica
    | 4 = Coordinador
    | 5 = Secretaría de Carrera
    |
    */

    private const ROL_SECRETARIA_GENERAL = 1;
    private const ROL_SECRETARIA_ACADEMICA = 3;
    private const ROL_COORDINADOR = 4;
    private const ROL_SECRETARIA_CARRERA = 5;

    protected Graficas $graficas;

    /**
     * Inyecta el modelo encargado de consultar
     * la información utilizada en los paneles.
     */
    public function __construct(Graficas $graficas)
    {
        $this->graficas = $graficas;
    }

    /**
     * Muestra el panel correspondiente al rol
     * del empleado autenticado.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect('/portal');
        }

        $usuario = Auth::user();
        $idRol = (int) ($usuario->id_rol ?? 0);

        $rolTexto = strtolower(
            trim(
                (string) (
                    session('rol_texto')
                    ?? session('tipo_usuario')
                    ?? $this->obtenerTextoRol($idRol)
                )
            )
        );

        $anio = (int) $request->input(
            'anio',
            now()->year
        );

        $aniosDisponibles = $this->graficas
            ->obtenerAniosDisponibles();

        $nombreUsuario = $this->obtenerNombreUsuario(
            $usuario
        );

        $data = [
            'titulo' => 'Gestión de Carrera - FCEAC',
            'userName' => $nombreUsuario,
            'userRole' => $rolTexto,
            'anio' => $anio,
            'aniosDisponibles' => $aniosDisponibles,
        ];

        return match ($idRol) {
            self::ROL_COORDINADOR =>
                $this->vistaCoordinador($data),

            self::ROL_SECRETARIA_CARRERA =>
                $this->vistaSecretariaCarrera($data),

            self::ROL_SECRETARIA_GENERAL,
            self::ROL_SECRETARIA_ACADEMICA =>
                $this->vistaSecretariaAcademica($data),

            default =>
                view('dashboard', $data),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | VISTA DEL COORDINADOR
    |--------------------------------------------------------------------------
    */

    /**
     * Muestra la vista principal del coordinador.
     *
     * Archivo:
     * resources/views/coordinador_carrera.blade.php
     */
    protected function vistaCoordinador(array $data): View
    {
        $idCarreraActual =
            $this->obtenerIdCarreraEmpleadoActual();

        $carreras = $this->obtenerCarrerasDelEmpleado(
            $idCarreraActual
        );

        return view(
            'coordinador_carrera',
            array_merge(
                $data,
                [
                    'carreras' => $carreras,
                    'idCarreraSeleccionada' =>
                        $idCarreraActual,
                ]
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VISTA DE SECRETARÍA DE CARRERA
    |--------------------------------------------------------------------------
    */

    /**
     * Muestra el panel principal de Secretaría de Carrera.
     */
    protected function vistaSecretariaCarrera(array $data): View
    {
        $idCarreraActual =
            $this->obtenerIdCarreraEmpleadoActual();

        $carreras = $this->obtenerCarrerasDelEmpleado(
            $idCarreraActual
        );

        return view(
            'secre_carrera',
            array_merge(
                $data,
                [
                    'carreras' => $carreras,
                    'idCarreraSeleccionada' =>
                        $idCarreraActual,
                ]
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VISTA DE SECRETARÍA GENERAL Y ACADÉMICA
    |--------------------------------------------------------------------------
    */

    /**
     * Muestra el panel principal de Secretaría General
     * o Secretaría Académica.
     */
    protected function vistaSecretariaAcademica(array $data): View
    {
        $departamentos = $this->graficas
            ->obtenerDepartamentosDisponibles();

        $idDepartamentoSeleccionado =
            request()->input('id_departamento');

        return view(
            'secre_academica',
            array_merge(
                $data,
                [
                    'departamentos' =>
                        $departamentos,

                    'idDepartamentoSeleccionado' =>
                        $idDepartamentoSeleccionado,
                ]
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | API AUXILIAR
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna las estadísticas generales del panel.
     */
    public function getEstadisticas(): JsonResponse
    {
        return response()->json([
            'aprobados' => 312,
        ]);
    }

    /**
     * Retorna el listado de empleados por unidad.
     */
    public function listarPorUnidad(): JsonResponse
    {
        return response()->json([]);
    }

    /**
     * Retorna las notificaciones del empleado.
     */
    public function getNotificaciones(): JsonResponse
    {
        return response()->json([]);
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODOS AUXILIARES
    |--------------------------------------------------------------------------
    */

    /**
     * Obtiene el nombre del usuario autenticado.
     */
    protected function obtenerNombreUsuario(
        mixed $usuario
    ): string {
        if (!$usuario) {
            return 'Usuario';
        }

        if (!empty($usuario->nombre_persona)) {
            return trim(
                (string) $usuario->nombre_persona
            );
        }

        if (!empty($usuario->name)) {
            return trim(
                (string) $usuario->name
            );
        }

        if (!empty($usuario->id_persona)) {
            $nombrePersona = DB::table('tbl_persona')
                ->where(
                    'id_persona',
                    (int) $usuario->id_persona
                )
                ->value('nombre_persona');

            if (!empty($nombrePersona)) {
                return trim(
                    (string) $nombrePersona
                );
            }
        }

        if (!empty($usuario->email)) {
            return trim(
                (string) $usuario->email
            );
        }

        return 'Usuario';
    }

    /**
     * Obtiene el identificador de la persona relacionada
     * con el usuario autenticado.
     */
    protected function obtenerIdPersonaAutenticada(): ?int
    {
        $usuario = Auth::user();

        if (
            !$usuario
            || empty($usuario->id_persona)
        ) {
            return null;
        }

        return (int) $usuario->id_persona;
    }

    /**
     * Obtiene la carrera asignada al empleado autenticado.
     */
    protected function obtenerIdCarreraEmpleadoActual(): ?int
    {
        $idPersona =
            $this->obtenerIdPersonaAutenticada();

        if ($idPersona === null) {
            return null;
        }

        $idCarrera = DB::table('tbl_empleados')
            ->where(
                'id_persona',
                $idPersona
            )
            ->value('id_carrera');

        return $idCarrera !== null
            ? (int) $idCarrera
            : null;
    }

    /**
     * Obtiene solamente la carrera asignada
     * al empleado autenticado.
     */
    protected function obtenerCarrerasDelEmpleado(
        ?int $idCarrera
    ): Collection {
        if ($idCarrera === null) {
            return collect();
        }

        $carrerasDisponibles = $this->graficas
            ->obtenerCarrerasDisponibles();

        $carrera = $carrerasDisponibles->firstWhere(
            'id_carrera',
            $idCarrera
        );

        if ($carrera === null) {
            return collect();
        }

        return collect([
            $carrera,
        ]);
    }

    /**
     * Devuelve el texto correspondiente al rol.
     */
    protected function obtenerTextoRol(int $idRol): string
    {
        return match ($idRol) {
            self::ROL_SECRETARIA_GENERAL =>
                'secretaria_general',

            self::ROL_SECRETARIA_ACADEMICA =>
                'secretaria_academica',

            self::ROL_COORDINADOR =>
                'coordinador',

            self::ROL_SECRETARIA_CARRERA =>
                'secretario',

            default =>
                'sin_rol',
        };
    }
}