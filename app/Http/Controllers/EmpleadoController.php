<?php

namespace App\Http\Controllers;

use App\Models\Graficas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    | 4 = Coordinador
    | 5 = Secretaría de Carrera
    |
    */

    private const ROL_SECRETARIA_GENERAL = 1;
    private const ROL_COORDINADOR = 4;
    private const ROL_SECRETARIA_CARRERA = 5;

    protected Graficas $graficas;

    /**
     * Inyección del modelo encargado de las gráficas.
     */
    public function __construct(Graficas $graficas)
    {
        $this->graficas = $graficas;
    }

    /**
     * Muestra o redirige al panel correspondiente
     * según el rol del empleado autenticado.
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('portal');
        }

        $user = Auth::user();

        /*
         * Se conserva el texto del rol para mostrarlo en las vistas,
         * pero la redirección se realiza usando el id_rol, ya que
         * resulta más seguro y evita problemas con nombres distintos.
         */
        $rolTexto = strtolower(
            trim(
                (string) (
                    session('rol_texto')
                    ?? $this->obtenerTextoRol((int) $user->id_rol)
                )
            )
        );

        $anio = $request->input('anio');

        $aniosDisponibles =
            $this->graficas->obtenerAniosDisponibles();

        $nombreUsuario =
            optional($user->persona)->nombre_persona
            ?? $user->nombre_persona
            ?? $user->name
            ?? 'Usuario';

        $data = [
            'titulo' => 'Gestión de Carrera - FCEAC',

            'userName' => $nombreUsuario,

            'userRole' => $rolTexto,

            'anio' => $anio,

            'aniosDisponibles' => $aniosDisponibles,
        ];

        return match ((int) $user->id_rol) {
            self::ROL_SECRETARIA_CARRERA =>
                $this->vistaSecretariaCarrera($data),

            self::ROL_SECRETARIA_GENERAL =>
                $this->vistaSecretariaAcademica($data),

            self::ROL_COORDINADOR =>
                $this->vistaCoordinador($data),

            default =>
                view('dashboard', $data),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | VISTA COORDINADOR
    |--------------------------------------------------------------------------
    |
    | Actualmente la vista principal disponible para el coordinador es:
    |
    | resources/views/bitacora_coordinador.blade.php
    |
    | Por ello, el dashboard redirige al módulo de bitácora.
    |
    */

    protected function vistaCoordinador(
        array $data
    ): RedirectResponse {
        return redirect()->route(
            'bitacora.coordinador'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VISTA SECRETARÍA DE CARRERA
    |--------------------------------------------------------------------------
    */

    protected function vistaSecretariaCarrera(
        array $data
    ): View {
        $idCarreraActual =
            $this->obtenerIdCarreraEmpleadoActual();

        $carreras = collect();

        if ($idCarreraActual) {
            $carrera = $this->graficas
                ->obtenerCarrerasDisponibles()
                ->firstWhere(
                    'id_carrera',
                    $idCarreraActual
                );

            if ($carrera) {
                $carreras = collect([
                    $carrera,
                ]);
            }
        }

        return view(
            'secre_carrera',
            array_merge(
                $data,
                [
                    'carreras' =>
                        $carreras,

                    'idCarreraSeleccionada' =>
                        $idCarreraActual,
                ]
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VISTA SECRETARÍA GENERAL
    |--------------------------------------------------------------------------
    */

    protected function vistaSecretariaAcademica(
        array $data
    ): View {
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

    public function getEstadisticas()
    {
        return response()->json([
            'aprobados' => 312,
        ]);
    }

    public function listarPorUnidad()
    {
        return response()->json([]);
    }

    public function getNotificaciones()
    {
        return response()->json([]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Obtiene el id de la persona vinculada al usuario autenticado.
     */
    protected function obtenerIdPersonaAutenticada(): ?int
    {
        $user = Auth::user();

        if (!$user || empty($user->id_persona)) {
            return null;
        }

        return (int) $user->id_persona;
    }

    /**
     * Obtiene la carrera asignada al empleado autenticado.
     */
    protected function obtenerIdCarreraEmpleadoActual(): ?int
    {
        $idPersona =
            $this->obtenerIdPersonaAutenticada();

        if (!$idPersona) {
            return null;
        }

        /*
         * Se consulta directamente la tabla para evitar posibles
         * conflictos de múltiples conjuntos de resultados al llamar
         * procedimientos almacenados desde Laravel.
         */
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
     * Devuelve un texto de rol cuando la sesión no contiene rol_texto.
     */
    protected function obtenerTextoRol(
        int $idRol
    ): string {
        return match ($idRol) {
            self::ROL_SECRETARIA_GENERAL =>
                'secretaria_general',

            self::ROL_COORDINADOR =>
                'coordinador',

            self::ROL_SECRETARIA_CARRERA =>
                'secretario',

            default =>
                'sin_rol',
        };
    }
}