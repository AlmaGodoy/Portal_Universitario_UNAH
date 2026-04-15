<?php

namespace App\Http\Controllers;

use App\Models\BackupLog;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BackupController extends Controller
{
    public function mostrarPanel()
    {
        $historial = BackupLog::orderByDesc('created_at')->get();

        $contextoVista = $this->resolverContextoVista();

        return view('backup_sistema', [
            'historial'      => $historial,
            'layout'         => $contextoVista['layout'],
            'dashboardRoute' => $contextoVista['dashboardRoute'],
        ]);
    }

    public function probarConexion()
    {
        try {
            DB::connection()->getPdo();

            return redirect()
                ->route('backup.index')
                ->with('success', '¡Conexión establecida exitosamente!');
        } catch (Throwable $e) {
            Log::error('Error al probar conexión de BD', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea'   => $e->getLine(),
            ]);

            return redirect()
                ->route('backup.index')
                ->with('error', 'Error: No se pudo conectar a la base de datos. ' . $e->getMessage());
        }
    }

    public function crearBackup()
    {
        try {
            $archivosAntes = $this->obtenerIndiceRespaldos();

            Artisan::call('backup:run', [
                '--only-db' => true,
            ]);

            $salida = trim(Artisan::output());
            $archivosDespues = $this->obtenerIndiceRespaldos();
            $backupGenerado = $this->detectarBackupGenerado($archivosAntes, $archivosDespues);

            if ($backupGenerado !== null) {
                BackupLog::create([
                    'nombre_archivo' => $backupGenerado['nombre_archivo'],
                    'tamano'         => $this->formatearTamano($backupGenerado['tamano_bytes']),
                    'usuario'        => $this->obtenerNombreUsuarioActual(),
                ]);
            }

            Log::info('Respaldo ejecutado correctamente', [
                'output'           => $salida,
                'backup_detectado' => $backupGenerado,
            ]);

            return redirect()
                ->route('backup.index')
                ->with(
                    'success',
                    $backupGenerado
                        ? 'El respaldo se ha generado y registrado correctamente.'
                        : 'El respaldo se generó correctamente, pero no se pudo registrar automáticamente en el historial.'
                );
        } catch (Throwable $e) {
            Log::error('Error al generar respaldo', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea'   => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('backup.index')
                ->with('error', 'Ocurrió un error al procesar el respaldo: ' . $e->getMessage());
        }
    }

    private function resolverContextoVista(): array
    {
        $user = auth()->user();

        $layout = 'layouts.app-estudiantes';
        $dashboardRoute = Route::has('dashboard')
            ? route('dashboard')
            : url('/dashboard');

        if (session('login_tipo') === 'empleado') {
            $dashboardRoute = Route::has('empleado.dashboard')
                ? route('empleado.dashboard')
                : url('/dashboard');

            $textoRol = $this->obtenerTextoRol($user);

            if ($this->contieneTodos($textoRol, ['secret', 'acad'])) {
                $layout = 'layouts.app-secretaria-academica';
            } elseif (str_contains($textoRol, 'general') && str_contains($textoRol, 'secret')) {
                $layout = 'layouts.app-secretaria-academica';
            } elseif (str_contains($textoRol, 'secret')) {
                $layout = 'layouts.app-secretaria';
            } elseif (str_contains($textoRol, 'coordin')) {
                $layout = 'layouts.app-coordinador';
            } else {
                $layout = 'layouts.app-coordinador';
            }
        }

        return [
            'layout'         => $layout,
            'dashboardRoute' => $dashboardRoute,
        ];
    }

    private function obtenerTextoRol($user): string
    {
        if (!$user) {
            return '';
        }

        $fragmentos = [
            data_get($user, 'rol'),
            data_get($user, 'nombre_rol'),
            data_get($user, 'role'),
            data_get($user, 'cargo'),
            data_get($user, 'tipo_usuario'),
            data_get($user, 'persona.rol'),
            data_get($user, 'persona.nombre_rol'),
            data_get($user, 'persona.role'),
            data_get($user, 'persona.cargo'),
            data_get($user, 'persona.tipo_usuario'),
        ];

        try {
            if (method_exists($user, 'roles')) {
                $roles = $user->roles;
                foreach ($roles as $rol) {
                    $fragmentos[] = data_get($rol, 'name');
                    $fragmentos[] = data_get($rol, 'nombre_rol');
                    $fragmentos[] = data_get($rol, 'role');
                }
            }
        } catch (Throwable $e) {
            Log::warning('No se pudo leer la colección de roles del usuario', [
                'mensaje' => $e->getMessage(),
            ]);
        }

        $texto = implode(' ', array_filter(array_map(function ($valor) {
            return is_scalar($valor) ? trim((string) $valor) : '';
        }, $fragmentos)));

        $texto = mb_strtolower($texto);

        return str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ'],
            ['a', 'e', 'i', 'o', 'u', 'n'],
            $texto
        );
    }

    private function contieneTodos(string $texto, array $fragmentos): bool
    {
        foreach ($fragmentos as $fragmento) {
            if (!str_contains($texto, $fragmento)) {
                return false;
            }
        }

        return true;
    }

    private function obtenerIndiceRespaldos(): array
    {
        $indice = [];

        $discos = config('backup.backup.destination.disks', ['local']);
        $nombreBackup = trim((string) config('backup.backup.name', ''), '/');

        foreach ($discos as $disco) {
            try {
                $storage = Storage::disk($disco);

                $archivos = $nombreBackup !== ''
                    ? $storage->allFiles($nombreBackup)
                    : $storage->allFiles();

                foreach ($archivos as $archivo) {
                    if (!preg_match('/\.(zip|sql|gz)$/i', $archivo)) {
                        continue;
                    }

                    $clave = $disco . '|' . $archivo;

                    $indice[$clave] = [
                        'disk'                => $disco,
                        'path'                => $archivo,
                        'nombre_archivo'      => basename($archivo),
                        'tamano_bytes'        => (int) $storage->size($archivo),
                        'ultima_modificacion' => (int) $storage->lastModified($archivo),
                    ];
                }
            } catch (Throwable $e) {
                Log::warning('No se pudo inspeccionar el disco de respaldos', [
                    'disk'    => $disco,
                    'mensaje' => $e->getMessage(),
                ]);
            }
        }

        uasort($indice, function ($a, $b) {
            return $b['ultima_modificacion'] <=> $a['ultima_modificacion'];
        });

        return $indice;
    }

    private function detectarBackupGenerado(array $antes, array $despues): ?array
    {
        $candidatos = [];

        foreach ($despues as $clave => $info) {
            if (!isset($antes[$clave])) {
                $candidatos[] = $info;
                continue;
            }

            if (($info['ultima_modificacion'] ?? 0) > ($antes[$clave]['ultima_modificacion'] ?? 0)) {
                $candidatos[] = $info;
            }
        }

        if (empty($candidatos)) {
            return null;
        }

        usort($candidatos, function ($a, $b) {
            return $b['ultima_modificacion'] <=> $a['ultima_modificacion'];
        });

        return $candidatos[0];
    }

    private function obtenerNombreUsuarioActual(): string
    {
        $user = auth()->user();

        if (!$user) {
            return 'Sistema';
        }

        if (isset($user->persona) && $user->persona && !empty($user->persona->nombre_persona)) {
            return trim($user->persona->nombre_persona);
        }

        if (!empty($user->nombre_persona)) {
            return trim($user->nombre_persona);
        }

        if (!empty($user->name)) {
            return trim($user->name);
        }

        if (!empty($user->email)) {
            return trim($user->email);
        }

        return 'Usuario autenticado';
    }

    private function formatearTamano(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        if ($bytes < 1024 * 1024 * 1024) {
            return number_format($bytes / (1024 * 1024), 2) . ' MB';
        }

        return number_format($bytes / (1024 * 1024 * 1024), 2) . ' GB';
    }
}
