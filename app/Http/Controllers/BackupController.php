<?php

namespace App\Http\Controllers;

use App\Models\BackupLog;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use ZipArchive;

class BackupController extends Controller
{
    public function mostrarPanel()
    {
        $historial = BackupLog::orderByDesc('id')->get();

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
            $backupGenerado = $this->generarRespaldo();

            BackupLog::create([
                'nombre_archivo' => $backupGenerado['nombre_archivo'],
                'tamano'         => $this->formatearTamano($backupGenerado['tamano_bytes']),
                'usuario'        => $this->obtenerNombreUsuarioActual(),
            ]);

            Log::info('Respaldo generado correctamente', [
                'backup' => $backupGenerado,
            ]);

            return redirect()
                ->route('backup.index')
                ->with('success', 'El respaldo se ha generado y registrado correctamente.');
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

    private function generarRespaldo(): array
    {
        try {
            if ($this->comandoArtisanExiste('backup:run')) {
                $antes = $this->obtenerBackupMasReciente();

                Artisan::call('backup:run', [
                    '--only-db' => true,
                ]);

                $salida = trim(Artisan::output());
                $despues = $this->obtenerBackupMasReciente();

                if ($despues) {
                    $esNuevo = !$antes
                        || ($antes['path'] ?? null) !== ($despues['path'] ?? null)
                        || ($antes['ultima_modificacion'] ?? 0) !== ($despues['ultima_modificacion'] ?? 0);

                    if ($esNuevo) {
                        $despues['metodo'] = 'artisan';
                        $despues['salida'] = $salida;

                        return $despues;
                    }
                }

                Log::warning('backup:run se ejecutó, pero no devolvió un archivo nuevo. Se usará respaldo manual.', [
                    'output' => $salida,
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('No se pudo generar el respaldo con backup:run. Se usará respaldo manual.', [
                'mensaje' => $e->getMessage(),
            ]);
        }

        return $this->generarRespaldoManual();
    }

    private function comandoArtisanExiste(string $comando): bool
    {
        try {
            $comandos = Artisan::all();

            return is_array($comandos) && array_key_exists($comando, $comandos);
        } catch (Throwable $e) {
            Log::warning('No se pudo validar si existe el comando Artisan.', [
                'comando' => $comando,
                'mensaje' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function generarRespaldoManual(): array
    {
        if (!function_exists('exec')) {
            throw new RuntimeException('La función exec() no está habilitada en PHP y es necesaria para generar el respaldo manual.');
        }

        $conexion = $this->obtenerConfiguracionConexionMysql();
        $disk = $this->obtenerDiscoBackup();
        $directorio = $this->obtenerDirectorioBackup();

        Storage::disk($disk)->makeDirectory($directorio);

        $nombreBase = $this->generarNombreBaseUnico($disk, $directorio);

        $rutaSqlRelativa = trim($directorio . '/' . $nombreBase . '.sql', '/');
        $rutaSqlAbsoluta = $this->resolverRutaAbsolutaDisco($disk, $rutaSqlRelativa);

        $ejecutable = $this->resolverRutaMySqlDump();

        $comando = $this->construirComandoMySqlDump(
            $ejecutable,
            $conexion,
            $rutaSqlAbsoluta
        );

        $salida = [];
        $codigo = 0;

        exec($comando . ' 2>&1', $salida, $codigo);

        clearstatcache(true, $rutaSqlAbsoluta);

        if ($codigo !== 0 || !is_file($rutaSqlAbsoluta) || filesize($rutaSqlAbsoluta) <= 0) {
            throw new RuntimeException(
                'No fue posible generar el respaldo manual. ' .
                implode(' ', array_filter($salida))
            );
        }

        $rutaFinalRelativa = $rutaSqlRelativa;
        $rutaFinalAbsoluta = $rutaSqlAbsoluta;

        if (class_exists(ZipArchive::class)) {
            $rutaZipRelativa = trim($directorio . '/' . $nombreBase . '.zip', '/');
            $rutaZipAbsoluta = $this->resolverRutaAbsolutaDisco($disk, $rutaZipRelativa);

            $zip = new ZipArchive();
            $resultadoZip = $zip->open($rutaZipAbsoluta, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            if ($resultadoZip === true) {
                $zip->addFile($rutaSqlAbsoluta, basename($rutaSqlRelativa));
                $zip->close();

                @unlink($rutaSqlAbsoluta);

                clearstatcache(true, $rutaZipAbsoluta);

                $rutaFinalRelativa = $rutaZipRelativa;
                $rutaFinalAbsoluta = $rutaZipAbsoluta;
            } else {
                Log::warning('No se pudo comprimir el respaldo en ZIP. Se conservará el archivo SQL.', [
                    'ruta_sql' => $rutaSqlRelativa,
                    'resultado_zip' => $resultadoZip,
                ]);
            }
        }

        if (!is_file($rutaFinalAbsoluta) || filesize($rutaFinalAbsoluta) <= 0) {
            throw new RuntimeException('El archivo de respaldo fue generado, pero no pudo localizarse para registrarlo.');
        }

        return [
            'disk'                => $disk,
            'path'                => $rutaFinalRelativa,
            'nombre_archivo'      => basename($rutaFinalRelativa),
            'tamano_bytes'        => (int) filesize($rutaFinalAbsoluta),
            'ultima_modificacion' => (int) filemtime($rutaFinalAbsoluta),
            'metodo'              => 'manual',
        ];
    }

    private function construirComandoMySqlDump(string $ejecutable, array $conexion, string $archivoSalida): string
    {
        $partes = [
            escapeshellarg($ejecutable),
            '--host=' . escapeshellarg((string) ($conexion['host'] ?? '127.0.0.1')),
            '--port=' . escapeshellarg((string) ($conexion['port'] ?? '3306')),
            '--user=' . escapeshellarg((string) ($conexion['username'] ?? 'root')),
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--routines',
            '--triggers',
            '--default-character-set=utf8mb4',
        ];

        $password = (string) ($conexion['password'] ?? '');

        if ($password !== '') {
            $partes[] = '--password=' . escapeshellarg($password);
        }

        $partes[] = '--result-file=' . escapeshellarg($archivoSalida);
        $partes[] = escapeshellarg((string) $conexion['database']);

        return implode(' ', $partes);
    }

    private function obtenerConfiguracionConexionMysql(): array
    {
        $conexionPorDefecto = config('database.default');
        $config = config('database.connections.' . $conexionPorDefecto);

        if (!is_array($config) || empty($config)) {
            throw new RuntimeException('No se encontró la configuración de conexión de la base de datos.');
        }

        $driver = strtolower((string) ($config['driver'] ?? ''));

        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException('El respaldo manual actualmente solo está preparado para MySQL/MariaDB.');
        }

        if (empty($config['database'])) {
            throw new RuntimeException('No se encontró el nombre de la base de datos en la configuración.');
        }

        return $config;
    }

    private function obtenerDiscoBackup(): string
    {
        $discosConfigurados = config('backup.backup.destination.disks', []);

        if (is_array($discosConfigurados)) {
            foreach ($discosConfigurados as $disk) {
                if (config('filesystems.disks.' . $disk)) {
                    return $disk;
                }
            }
        }

        return 'local';
    }

    private function obtenerDirectorioBackup(): string
    {
        $nombre = trim((string) config('backup.backup.name', 'Laravel'));

        return $nombre !== '' ? trim($nombre, '/') : 'Laravel';
    }

    private function resolverRutaAbsolutaDisco(string $disk, string $rutaRelativa): string
    {
        $storage = Storage::disk($disk);

        if (method_exists($storage, 'path')) {
            return $storage->path($rutaRelativa);
        }

        return storage_path('app/' . ltrim($rutaRelativa, '/'));
    }

    private function resolverRutaMySqlDump(): string
    {
        $candidatos = array_filter([
            env('MYSQLDUMP_PATH'),
            config('database.mysqldump_path'),
            PHP_OS_FAMILY === 'Windows' ? 'C:\\xampp\\mysql\\bin\\mysqldump.exe' : null,
            PHP_OS_FAMILY === 'Windows' ? 'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe' : null,
            PHP_OS_FAMILY === 'Windows' ? 'C:\\Program Files\\MariaDB 10.4\\bin\\mysqldump.exe' : null,
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/opt/homebrew/bin/mysqldump',
            'mysqldump',
        ]);

        foreach ($candidatos as $candidato) {
            if ($candidato === 'mysqldump') {
                return $candidato;
            }

            if (is_string($candidato) && $candidato !== '' && file_exists($candidato)) {
                return $candidato;
            }
        }

        return 'mysqldump';
    }

    private function generarNombreBaseUnico(string $disk, string $directorio): string
    {
        $base = now()->format('Y-m-d-H-i-s');
        $nombre = $base;
        $contador = 1;

        while (
            Storage::disk($disk)->exists(trim($directorio . '/' . $nombre . '.zip', '/')) ||
            Storage::disk($disk)->exists(trim($directorio . '/' . $nombre . '.sql', '/'))
        ) {
            $nombre = $base . '-' . $contador;
            $contador++;
        }

        return $nombre;
    }

    private function obtenerBackupMasReciente(): ?array
    {
        $indice = [];

        $discos = config('backup.backup.destination.disks', ['local']);
        $nombreBackup = trim((string) config('backup.backup.name', 'Laravel'), '/');

        foreach ($discos as $disco) {
            try {
                if (!config('filesystems.disks.' . $disco)) {
                    continue;
                }

                $storage = Storage::disk($disco);

                $rutas = array_unique(array_filter([
                    $nombreBackup,
                    '',
                ], fn ($item) => $item !== null));

                foreach ($rutas as $rutaBase) {
                    $archivos = $rutaBase !== ''
                        ? $storage->allFiles($rutaBase)
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
                }
            } catch (Throwable $e) {
                Log::warning('No se pudo inspeccionar el disco de respaldos', [
                    'disk'    => $disco,
                    'mensaje' => $e->getMessage(),
                ]);
            }
        }

        if (empty($indice)) {
            return null;
        }

        $respaldos = array_values($indice);

        usort($respaldos, function ($a, $b) {
            return ($b['ultima_modificacion'] ?? 0) <=> ($a['ultima_modificacion'] ?? 0);
        });

        return $respaldos[0] ?? null;
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
