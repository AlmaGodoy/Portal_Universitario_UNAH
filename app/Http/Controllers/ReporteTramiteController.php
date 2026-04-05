<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReporteTramitesExport;
use App\Models\ReporteTramite;

class ReporteTramiteController extends Controller
{
    /**
     * API / JSON para Postman
     */
    public function reporte(Request $request)
    {
        $request->validate([
            'tipo_tramite'      => 'nullable|string|max:50',
            'estado_resolucion' => 'nullable|string|max:20',
            'mes_reporte'       => 'nullable|integer|min:1|max:12',
            'id_carrera'        => 'nullable'
        ]);

        try {
            $datos = $this->resolverDatosReporteSegunRol($request);

            return response()->json([
                'total_registros' => count($datos['tramitesReporte']),
                'data'            => $datos['tramitesReporte'],
                'resumen'         => [
                    'aprobadosMes'    => $datos['aprobadosMes'],
                    'rechazadosMes'   => $datos['rechazadosMes'],
                    'pendientesTotal' => $datos['pendientesTotal'],
                    'revisionTotal'   => $datos['revisionTotal'],
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vista del módulo de reportes para secretario/coordinador
     */
    public function vistaReporte(Request $request)
    {
        try {
            $datos = $this->obtenerDatosReporte($request);

            return view('Reporte', array_merge($datos, $this->obtenerDatosLayoutEmpleado()));

        } catch (\Exception $e) {
            return view('Reporte', array_merge([
                'mesActual'          => ucfirst(Carbon::now()->locale('es')->translatedFormat('F Y')),
                'aprobadosMes'       => 0,
                'rechazadosMes'      => 0,
                'pendientesTotal'    => 0,
                'revisionTotal'      => 0,
                'tramitesPendientes' => [],
                'tramitesReporte'    => [],
                'tipoTramite'        => '',
                'estadoResolucion'   => '',
                'mesReporte'         => '',
                'error'              => $e->getMessage()
            ], $this->obtenerDatosLayoutEmpleado()));
        }
    }

    /**
     * Vista del módulo de reportes para Secretaría General
     */
    public function vistaReporteSecretariaGeneral(Request $request)
    {
        try {
            $datos = $this->obtenerDatosReporteSecretariaGeneral($request);

            return view('ReporteSecretariaGeneral', array_merge($datos, $this->obtenerDatosLayoutEmpleado()));

        } catch (\Exception $e) {
            return view('ReporteSecretariaGeneral', array_merge([
                'mesActual'        => ucfirst(Carbon::now()->locale('es')->translatedFormat('F Y')),
                'aprobadosMes'     => 0,
                'rechazadosMes'    => 0,
                'pendientesTotal'  => 0,
                'revisionTotal'    => 0,
                'tramitesReporte'  => [],
                'tipoTramite'      => '',
                'estadoResolucion' => '',
                'mesReporte'       => '',
                'idCarrera'        => '',
                'carreras'         => collect(),
                'error'            => $e->getMessage()
            ], $this->obtenerDatosLayoutEmpleado()));
        }
    }

    /**
     * Exportar a PDF
     */
    public function exportarPdf(Request $request)
    {
        try {
            $datos = $this->resolverDatosReporteSegunRol($request);

            $pdf = Pdf::loadView('reportepdf', array_merge($datos, $this->obtenerDatosLayoutEmpleado()))
                ->setPaper('a4', 'portrait');

            return $pdf->download('reporte_tramites.pdf');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'No se pudo generar el PDF: ' . $e->getMessage());
        }
    }

    /**
     * Exportar a Excel
     */
    public function exportarExcel(Request $request)
    {
        try {
            $datos = $this->resolverDatosReporteSegunRol($request);

            return Excel::download(
                new ReporteTramitesExport($datos['tramitesReporte']),
                'reporte_tramites.xlsx'
            );

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'No se pudo generar el Excel: ' . $e->getMessage());
        }
    }

    /**
     * Decide qué método usar según el rol autenticado
     */
    private function resolverDatosReporteSegunRol(Request $request): array
    {
        $rol = strtolower((string) (session('rol_texto') ?? ''));

        if ($rol === 'secretaria_general') {
            return $this->obtenerDatosReporteSecretariaGeneral($request);
        }

        return $this->obtenerDatosReporte($request);
    }

    /**
     * Método interno reutilizable para secretario/coordinador
     */
    private function obtenerDatosReporte(Request $request)
    {
        $idPersonaEmpleado = $this->obtenerIdPersonaEmpleadoAutenticado();

        $tipoTramite = strtolower(trim($request->input('tipo_tramite', '')));
        $estadoResolucion = strtolower(trim($request->input('estado_resolucion', '')));
        $mesReporte = $request->input('mes_reporte');

        $tipoTramiteParam = !empty($tipoTramite) ? $tipoTramite : null;
        $estadoResolucionParam = !empty($estadoResolucion) ? $estadoResolucion : null;

        $resultado = ReporteTramite::obtener(
            $idPersonaEmpleado,
            $tipoTramiteParam,
            $estadoResolucionParam
        );

        $aprobadosMes = 0;
        $rechazadosMes = 0;
        $pendientesTotal = 0;
        $revisionTotal = 0;

        $tramitesPendientes = [];
        $tramitesReporte = [];

        foreach ($resultado as $tramite) {
            $estado = strtolower(trim($tramite->estado_actual ?? ''));
            $fechaSolicitud = $tramite->fecha_solicitud ?? null;

            $cumpleMesFiltro = true;

            if (!empty($mesReporte) && !empty($fechaSolicitud)) {
                try {
                    $fechaTramiteFiltro = Carbon::parse($fechaSolicitud);
                    $cumpleMesFiltro = ((int) $fechaTramiteFiltro->month === (int) $mesReporte);
                } catch (\Exception $e) {
                    $cumpleMesFiltro = false;
                }
            }

            if (!$cumpleMesFiltro) {
                continue;
            }

            $tramitesReporte[] = [
                'estudiante'      => $tramite->estudiante ?? 'Sin nombre',
                'tipo'            => $tramite->tipo ?? 'No definido',
                'fecha_solicitud' => $tramite->fecha_solicitud ?? 'Sin fecha',
                'estado'          => $tramite->estado_actual ?? 'pendiente'
            ];

            if ($estado === 'aprobada' || $estado === 'aprobado') {
                $aprobadosMes++;
            }

            if ($estado === 'rechazada' || $estado === 'rechazado') {
                $rechazadosMes++;
            }

            if ($estado === 'pendiente') {
                $pendientesTotal++;

                $tramitesPendientes[] = [
                    'estudiante'      => $tramite->estudiante ?? 'Sin nombre',
                    'tipo'            => $tramite->tipo ?? 'No definido',
                    'fecha_solicitud' => $tramite->fecha_solicitud ?? 'Sin fecha',
                    'estado'          => $tramite->estado_actual ?? 'pendiente'
                ];
            }

            if ($estado === 'revision' || $estado === 'revisión') {
                $revisionTotal++;
            }
        }

        return [
            'mesActual'          => ucfirst(Carbon::now()->locale('es')->translatedFormat('F Y')),
            'aprobadosMes'       => $aprobadosMes,
            'rechazadosMes'      => $rechazadosMes,
            'pendientesTotal'    => $pendientesTotal,
            'revisionTotal'      => $revisionTotal,
            'tramitesPendientes' => $tramitesPendientes,
            'tramitesReporte'    => $tramitesReporte,
            'tipoTramite'        => $tipoTramite,
            'estadoResolucion'   => $estadoResolucion,
            'mesReporte'         => $mesReporte
        ];
    }

    /**
     * Método interno para Secretaría General
     */
    private function obtenerDatosReporteSecretariaGeneral(Request $request)
    {
        $idCarrera = $request->input('id_carrera');
        $tipoTramite = strtolower(trim($request->input('tipo_tramite', '')));
        $estadoResolucion = strtolower(trim($request->input('estado_resolucion', '')));
        $mesReporte = $request->input('mes_reporte');

        $idCarreraParam = (!empty($idCarrera) && $idCarrera !== '0') ? (int) $idCarrera : null;
        $tipoTramiteParam = !empty($tipoTramite) ? $tipoTramite : null;
        $estadoResolucionParam = !empty($estadoResolucion) ? $estadoResolucion : null;
        $mesReporteParam = (!empty($mesReporte) && $mesReporte !== '0') ? (int) $mesReporte : null;

        $resultado = ReporteTramite::obtenerSecretariaGeneral(
            $idCarreraParam,
            $tipoTramiteParam,
            $estadoResolucionParam,
            $mesReporteParam
        );

        $carreras = DB::table('tbl_carrera')
            ->select('id_carrera', 'nombre_carrera')
            ->orderBy('nombre_carrera', 'asc')
            ->get();

        $aprobadosMes = 0;
        $rechazadosMes = 0;
        $pendientesTotal = 0;
        $revisionTotal = 0;
        $tramitesReporte = [];

        foreach ($resultado as $tramite) {
            $estado = strtolower(trim($tramite->estado_actual ?? ''));

            $tramitesReporte[] = [
                'estudiante'      => $tramite->estudiante ?? 'Sin nombre',
                'carrera'         => $tramite->carrera ?? 'Sin carrera',
                'tipo'            => $tramite->tipo ?? 'No definido',
                'fecha_solicitud' => $tramite->fecha_solicitud ?? 'Sin fecha',
                'estado'          => $tramite->estado_actual ?? 'pendiente'
            ];

            if ($estado === 'aprobada' || $estado === 'aprobado') {
                $aprobadosMes++;
            }

            if ($estado === 'rechazada' || $estado === 'rechazado') {
                $rechazadosMes++;
            }

            if ($estado === 'pendiente') {
                $pendientesTotal++;
            }

            if ($estado === 'revision' || $estado === 'revisión') {
                $revisionTotal++;
            }
        }

        return [
            'mesActual'        => ucfirst(Carbon::now()->locale('es')->translatedFormat('F Y')),
            'aprobadosMes'     => $aprobadosMes,
            'rechazadosMes'    => $rechazadosMes,
            'pendientesTotal'  => $pendientesTotal,
            'revisionTotal'    => $revisionTotal,
            'tramitesReporte'  => $tramitesReporte,
            'tipoTramite'      => $tipoTramite,
            'estadoResolucion' => $estadoResolucion,
            'mesReporte'       => $mesReporte,
            'idCarrera'        => $idCarrera,
            'carreras'         => $carreras
        ];
    }

    /**
     * Obtener datos del layout de empleado
     */
    private function obtenerDatosLayoutEmpleado(): array
    {
        $user = Auth::user();

        return [
            'titulo'   => 'Gestión de Carrera - FCEAC',
            'userName' => $user->name ?? ($user->nombre ?? 'Usuario'),
            'userRole' => session('rol_texto') ?? 'empleado',
        ];
    }

    /**
     * Obtener el id_persona del usuario autenticado
     */
    private function obtenerIdPersonaEmpleadoAutenticado(): ?int
    {
        if (!Auth::check()) {
            return null;
        }

        return Auth::user()->id_persona ?? null;
    }
}
