<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BackupLog;
use Illuminate\Support\Facades\Artisan;

class BackupController extends Controller
{
    /**
     * Muestra la pantalla principal del respaldo.
     */
    public function mostrarPanel()
    {
        // Trae los registros de la tabla backup_logs
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
        } catch (\Exception $e) {
            return back()->with('error', 'Error: No se pudo conectar a la base de datos.');
        }
    }

    /**
     * Lógica para el botón "Respaldar"
     */
    public function crearBackup()
    {
        try {
            // Comando que genera el archivo físico de la base de datos
            Artisan::call('backup:run --only-db');

            return back()->with('success', 'El respaldo se ha generado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error al procesar el respaldo.');
        }
    }
}
