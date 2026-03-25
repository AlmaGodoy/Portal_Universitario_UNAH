<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReporteTramitesExport;

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
            'mes_reporte'       => 'nullable|integer|min:1|max:12'
        ]);

        try {
            $resultado = DB::select('CALL SEL_REPORTE_TRAMITE(?, ?)', [null, null]);

            $tipoTramite = strtolower(trim($request->input('tipo_tramite', '')));
            $estadoResolucion = strtolower(trim($request->input('estado_resolucion', '')));
            $mesReporte = $request->input('mes_reporte');

            $filtrados = [];

            foreach ($resultado as $tramite) {
                $tipo = strtolower(trim($tramite->tipo ?? ''));
                $estado = strtolower(trim($tramite->estado_actual ?? ''));
                $fechaSolicitud = $tramite->fecha_solicitud ?? null;

                $cumpleTipo = empty($tipoTramite) || $tipo === $tipoTramite;
                $cumpleEstado = empty($estadoResolucion) || $estado === $estadoResolucion;
                $cumpleMes = true;

                if (!empty($mesReporte) && !empty($fechaSolicitud)) {
                    try {
                        $fechaTramite = Carbon::parse($fechaSolicitud);
                        $cumpleMes = ((int)$fechaTramite->month === (int)$mesReporte);
                    } catch (\Exception $e) {
                        $cumpleMes = false;
                    }
                }

                if ($cumpleTipo && $cumpleEstado && $cumpleMes) {
                    $filtrados[] = $tramite;
                }
            }

            return response()->json([
                'total_registros' => count($filtrados),
                'data' => $filtrados
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'resultado' => 'ERROR',
                'mensaje'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vista del módulo de reportes
     */
    public function vistaReporte(Request $request)
    {
        try {
            $datos = $this->obtenerDatosReporte($request);
            return view('Reporte', $datos);

        } catch (\Exception $e) {
            return view('Reporte', [
                'mesActual' => ucfirst(Carbon::now()->locale('es')->translatedFormat('F Y')),
                'aprobadosMes' => 0,
                'rechazadosMes' => 0,
                'pendientesTotal' => 0,
                'tramitesPendientes' => [],
                'tipoTramite' => '',
                'estadoResolucion' => '',
                'mesReporte' => '',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Exportar a PDF
     */
    public function exportarPdf(Request $request)
    {
        try {
            $datos = $this->obtenerDatosReporte($request);

            $pdf = Pdf::loadView('ReportePdf', $datos)
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
            $datos = $this->obtenerDatosReporte($request);

            return Excel::download(
                new ReporteTramitesExport($datos['tramitesPendientes']),
                'reporte_tramites.xlsx'
            );

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'No se pudo generar el Excel: ' . $e->getMessage());
        }
    }

    /**
     * Método interno reutilizable para vista, PDF y Excel
     */
    private function obtenerDatosReporte(Request $request)
    {
        $resultado = DB::select('CALL SEL_REPORTE_TRAMITE(?, ?)', [null, null]);

        $tipoTramite = strtolower(trim($request->input('tipo_tramite', '')));
        $estadoResolucion = strtolower(trim($request->input('estado_resolucion', '')));
        $mesReporte = $request->input('mes_reporte');

        $aprobadosMes = 0;
        $rechazadosMes = 0;
        $tramitesPendientes = [];

        $mesActualNumero = now()->month;
        $anioActual = now()->year;

        foreach ($resultado as $tramite) {
            $tipo = strtolower(trim($tramite->tipo ?? ''));
            $estado = strtolower(trim($tramite->estado_actual ?? ''));
            $fechaSolicitud = $tramite->fecha_solicitud ?? null;

            $cumpleTipo = empty($tipoTramite) || $tipo === $tipoTramite;
            $cumpleEstado = empty($estadoResolucion) || $estado === $estadoResolucion;
            $cumpleMesFiltro = true;

            if (!empty($mesReporte) && !empty($fechaSolicitud)) {
                try {
                    $fechaTramiteFiltro = Carbon::parse($fechaSolicitud);
                    $cumpleMesFiltro = ((int)$fechaTramiteFiltro->month === (int)$mesReporte);
                } catch (\Exception $e) {
                    $cumpleMesFiltro = false;
                }
            }

            if (!($cumpleTipo && $cumpleEstado && $cumpleMesFiltro)) {
                continue;
            }

            if (!empty($fechaSolicitud)) {
                try {
                    $fechaTramite = Carbon::parse($fechaSolicitud);
                    $mesBaseConteo = !empty($mesReporte) ? (int)$mesReporte : $mesActualNumero;

                    if ((int)$fechaTramite->month === $mesBaseConteo && (int)$fechaTramite->year === $anioActual) {
                        if ($estado === 'aprobado') {
                            $aprobadosMes++;
                        }

                        if ($estado === 'rechazado') {
                            $rechazadosMes++;
                        }
                    }
                } catch (\Exception $e) {
                    // Ignorar fechas inválidas
                }
            }

            if ($estado === 'pendiente') {
                $tramitesPendientes[] = [
                    'estudiante'      => $tramite->estudiante ?? 'Sin nombre',
                    'tipo'            => $tramite->tipo ?? 'No definido',
                    'fecha_solicitud' => $tramite->fecha_solicitud ?? 'Sin fecha',
                    'estado'          => $tramite->estado_actual ?? 'pendiente'
                ];
            }
        }

        return [
            'mesActual' => ucfirst(Carbon::now()->locale('es')->translatedFormat('F Y')),
            'aprobadosMes' => $aprobadosMes,
            'rechazadosMes' => $rechazadosMes,
            'pendientesTotal' => count($tramitesPendientes),
            'tramitesPendientes' => $tramitesPendientes,
            'tipoTramite' => $tipoTramite,
            'estadoResolucion' => $estadoResolucion,
            'mesReporte' => $mesReporte
        ];
    }
}
