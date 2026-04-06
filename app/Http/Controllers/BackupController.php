<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\BackupLog;
use Throwable;

class BackupController extends Controller
{
    /**
     * Muestra la pantalla principal del respaldo.
     */
    public function mostrarPanel()
    {
        $historial = BackupLog::orderBy('created_at', 'desc')->get();

        return view('backup_sistema', compact('historial'));
    }

    /**
     * Lógica para el botón "Probar Conexión"
     */
    public function probarConexion()
    {
        try {
            DB::connection()->getPdo();

            return back()->with('success', '¡Conexión establecida exitosamente!');
        } catch (Throwable $e) {
            Log::error('Error al probar conexión de BD', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea'   => $e->getLine(),
            ]);

            return back()->with('error', 'Error: No se pudo conectar a la base de datos. ' . $e->getMessage());
        }
    }

    /**
     * Lógica para el botón "Respaldar"
     */
    public function crearBackup()
    {
        try {
            Artisan::call('backup:run', [
                '--only-db' => true,
            ]);

            $salida = Artisan::output();

            Log::info('Respaldo ejecutado correctamente', [
                'output' => $salida,
            ]);

            return back()->with('success', 'El respaldo se ha generado correctamente.');
        } catch (Throwable $e) {
            Log::error('Error al generar respaldo', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea'   => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Ocurrió un error al procesar el respaldo: ' . $e->getMessage());
        }
    }
}