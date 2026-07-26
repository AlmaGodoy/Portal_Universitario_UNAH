<?php

namespace App\Http\Controllers;

use App\Models\BackupLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use ZipArchive;

class BackupController extends Controller
{
    /**
     * Mostrar el panel principal de respaldos.
     */
    public function mostrarPanel(): View
    {
        $modeloBackup = new BackupLog();

        $historial = BackupLog::query()
            ->orderByDesc($modeloBackup->getKeyName())
            ->get();

        $contextoVista = $this->resolverContextoVista();

        return view('backup_sistema', [
            'historial'      => $historial,
            'layout'         => $contextoVista['layout'],
            'dashboardRoute' => $contextoVista['dashboardRoute'],
        ]);
    }

    /**
     * Probar la conexión con la base de datos.
     */
    public function probarConexion(): RedirectResponse
    {
        try {
            DB::connection()->getPdo();

            return redirect()
                ->route('backup.index')
                ->with(
                    'success',
                    '¡La conexión con la base de datos se estableció correctamente!'
                );
        } catch (Throwable $e) {
            Log::error('Error al probar la conexión con la base de datos.', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea'   => $e->getLine(),
            ]);

            return redirect()
                ->route('backup.index')
                ->with(
                    'error',
                    'No se pudo establecer la conexión con la base de datos: '
                    . $e->getMessage()
                );
        }
    }

    /**
     * Generar, registrar y descargar un respaldo.
     */
    public function crearBackup(): StreamedResponse|RedirectResponse
    {
        try {
            /*
             * Generar el archivo de respaldo.
             */
            $backupGenerado = $this->generarRespaldo();

            $disk = (string) $backupGenerado['disk'];
            $path = (string) $backupGenerado['path'];
            $nombreArchivo = (string) $backupGenerado['nombre_archivo'];

            /*
             * Definir explícitamente el tipo para que Intelephense
             * reconozca los métodos exists(), size() y download().
             */
            /** @var FilesystemAdapter $storage */
            $storage = Storage::disk($disk);

            if (!$storage->exists($path)) {
                throw new RuntimeException(
                    'El proceso terminó, pero no se encontró el archivo de respaldo.'
                );
            }

            $tamanoBytes = (int) $storage->size($path);

            if ($tamanoBytes <= 0) {
                throw new RuntimeException(
                    'El archivo de respaldo fue creado, pero está vacío.'
                );
            }

            /*
             * Registrar el respaldo antes de enviarlo al navegador.
             */
            $registro = new BackupLog();
            $registro->nombre_archivo = $nombreArchivo;
            $registro->tamano = $this->formatearTamano($tamanoBytes);
            $registro->usuario = $this->obtenerNombreUsuarioActual();
            $registro->save();

            Log::info(
                'Respaldo generado, registrado y preparado para descarga.',
                [
                    'registro_id'    => $registro->getKey(),
                    'disk'           => $disk,
                    'path'           => $path,
                    'nombre_archivo' => $nombreArchivo,
                    'tamano_bytes'   => $tamanoBytes,
                    'metodo'         => $backupGenerado['metodo']
                        ?? 'desconocido',
                ]
            );

            /*
             * Devolver el archivo como descarga.
             *
             * No se elimina después de descargarlo para que permanezca
             * disponible en storage/app/backups.
             */
            return $storage->download(
                $path,
                $nombreArchivo,
                [
                    'Content-Type' => $this->obtenerTipoContenido(
                        $nombreArchivo
                    ),
                    'Cache-Control' => 'no-store, no-cache, must-revalidate',
                    'Pragma'        => 'no-cache',
                ]
            );
        } catch (Throwable $e) {
            Log::error('Error al generar o descargar el respaldo.', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea'   => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('backup.index')
                ->with(
                    'error',
                    'No fue posible generar el respaldo: '
                    . $e->getMessage()
                );
        }
    }

    /**
     * Generar el respaldo utilizando Spatie Laravel Backup.
     *
     * Si Spatie falla, se intenta generar manualmente.
     */
    private function generarRespaldo(): array
    {
        try {
            $antes = $this->obtenerBackupMasReciente();

            $codigoSalida = Artisan::call('backup:run', [
                '--only-db'               => true,
                '--disable-notifications' => true,
            ]);

            $salidaArtisan = trim(Artisan::output());

            if ($codigoSalida !== 0) {
                throw new RuntimeException(
                    'El comando backup:run terminó con código '
                    . $codigoSalida
                    . '. '
                    . $salidaArtisan
                );
            }

            /*
             * Esperar brevemente mientras termina de escribirse el ZIP.
             */
            usleep(300000);
            clearstatcache();

            $despues = $this->obtenerBackupMasReciente();

            if (!$despues) {
                throw new RuntimeException(
                    'Spatie terminó correctamente, pero no se encontró '
                    . 'el archivo de respaldo generado. '
                    . $salidaArtisan
                );
            }

            $esNuevo = !$antes
                || ($antes['disk'] ?? null)
                    !== ($despues['disk'] ?? null)
                || ($antes['path'] ?? null)
                    !== ($despues['path'] ?? null)
                || ($antes['ultima_modificacion'] ?? 0)
                    !== ($despues['ultima_modificacion'] ?? 0)
                || ($antes['tamano_bytes'] ?? 0)
                    !== ($despues['tamano_bytes'] ?? 0);

            if (!$esNuevo) {
                throw new RuntimeException(
                    'El comando backup:run se ejecutó, pero no se detectó '
                    . 'un archivo de respaldo nuevo. '
                    . $salidaArtisan
                );
            }

            $despues['metodo'] = 'spatie';
            $despues['salida'] = $salidaArtisan;

            return $despues;
        } catch (Throwable $e) {
            Log::warning(
                'No se pudo generar el respaldo mediante Spatie. '
                . 'Se intentará el método manual.',
                [
                    'mensaje' => $e->getMessage(),
                ]
            );
        }

        return $this->generarRespaldoManual();
    }

    /**
     * Generar respaldo mediante mariadb-dump o mysqldump.
     */
    private function generarRespaldoManual(): array
    {
        if (!function_exists('exec')) {
            throw new RuntimeException(
                'La función exec() no está habilitada en PHP.'
            );
        }

        $conexion = $this->obtenerConfiguracionConexionMysql();
        $disk = $this->obtenerDiscoBackup();
        $directorio = $this->obtenerDirectorioBackup();

        /** @var FilesystemAdapter $storage */
        $storage = Storage::disk($disk);

        $storage->makeDirectory($directorio);

        $nombreBase = $this->generarNombreBaseUnico(
            $disk,
            $directorio
        );

        $rutaSqlRelativa = trim(
            $directorio . '/' . $nombreBase . '.sql',
            '/'
        );

        $rutaSqlAbsoluta = $this->resolverRutaAbsolutaDisco(
            $disk,
            $rutaSqlRelativa
        );

        $ejecutable = $this->resolverRutaMySqlDump();

        $comando = $this->construirComandoMySqlDump(
            $ejecutable,
            $conexion,
            $rutaSqlAbsoluta
        );

        $salida = [];
        $codigoSalida = 0;

        exec(
            $comando . ' 2>&1',
            $salida,
            $codigoSalida
        );

        clearstatcache(true, $rutaSqlAbsoluta);

        if (
            $codigoSalida !== 0
            || !is_file($rutaSqlAbsoluta)
            || filesize($rutaSqlAbsoluta) <= 0
        ) {
            throw new RuntimeException(
                'No fue posible generar el respaldo manual. Código: '
                . $codigoSalida
                . '. Salida: '
                . implode(' ', array_filter($salida))
            );
        }

        $rutaFinalRelativa = $rutaSqlRelativa;
        $rutaFinalAbsoluta = $rutaSqlAbsoluta;

        /*
         * Comprimir el archivo SQL cuando ZipArchive esté disponible.
         */
        if (class_exists(ZipArchive::class)) {
            $rutaZipRelativa = trim(
                $directorio . '/' . $nombreBase . '.zip',
                '/'
            );

            $rutaZipAbsoluta = $this->resolverRutaAbsolutaDisco(
                $disk,
                $rutaZipRelativa
            );

            $zip = new ZipArchive();

            $resultadoZip = $zip->open(
                $rutaZipAbsoluta,
                ZipArchive::CREATE | ZipArchive::OVERWRITE
            );

            if ($resultadoZip === true) {
                $archivoAgregado = $zip->addFile(
                    $rutaSqlAbsoluta,
                    basename($rutaSqlRelativa)
                );

                $zipCerrado = $zip->close();

                if (
                    $archivoAgregado
                    && $zipCerrado
                    && is_file($rutaZipAbsoluta)
                    && filesize($rutaZipAbsoluta) > 0
                ) {
                    @unlink($rutaSqlAbsoluta);

                    clearstatcache(
                        true,
                        $rutaZipAbsoluta
                    );

                    $rutaFinalRelativa = $rutaZipRelativa;
                    $rutaFinalAbsoluta = $rutaZipAbsoluta;
                }
            } else {
                Log::warning(
                    'No se pudo comprimir el respaldo. '
                    . 'Se conservará como archivo SQL.',
                    [
                        'resultado_zip' => $resultadoZip,
                        'ruta_sql'      => $rutaSqlRelativa,
                    ]
                );
            }
        }

        if (
            !is_file($rutaFinalAbsoluta)
            || filesize($rutaFinalAbsoluta) <= 0
        ) {
            throw new RuntimeException(
                'El respaldo fue generado, pero no pudo localizarse.'
            );
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

    /**
     * Construir el comando de respaldo manual.
     */
    private function construirComandoMySqlDump(
        string $ejecutable,
        array $conexion,
        string $archivoSalida
    ): string {
        $partes = [
            escapeshellarg($ejecutable),

            '--host=' . escapeshellarg(
                (string) ($conexion['host'] ?? '127.0.0.1')
            ),

            '--port=' . escapeshellarg(
                (string) ($conexion['port'] ?? '3306')
            ),

            '--user=' . escapeshellarg(
                (string) ($conexion['username'] ?? 'root')
            ),

            /*
             * Compatible con el cliente MariaDB del contenedor.
             */
            '--skip-ssl',

            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--routines',
            '--triggers',
            '--default-character-set=utf8mb4',
        ];

        $password = (string) ($conexion['password'] ?? '');

        if ($password !== '') {
            $partes[] = '--password=' . escapeshellarg(
                $password
            );
        }

        $partes[] = '--result-file=' . escapeshellarg(
            $archivoSalida
        );

        $partes[] = escapeshellarg(
            (string) $conexion['database']
        );

        return implode(' ', $partes);
    }

    /**
     * Obtener la configuración de la conexión predeterminada.
     */
    private function obtenerConfiguracionConexionMysql(): array
    {
        $conexionPorDefecto = (string) config(
            'database.default'
        );

        $config = config(
            'database.connections.' . $conexionPorDefecto
        );

        if (!is_array($config) || empty($config)) {
            throw new RuntimeException(
                'No se encontró la configuración de la base de datos.'
            );
        }

        $driver = strtolower(
            (string) ($config['driver'] ?? '')
        );

        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException(
                'El respaldo manual solamente admite MySQL o MariaDB.'
            );
        }

        if (empty($config['database'])) {
            throw new RuntimeException(
                'No se encontró el nombre de la base de datos.'
            );
        }

        return $config;
    }

    /**
     * Obtener el disco configurado para respaldos.
     */
    private function obtenerDiscoBackup(): string
    {
        $discos = config(
            'backup.backup.destination.disks',
            ['local']
        );

        if (is_array($discos)) {
            foreach ($discos as $disk) {
                if (
                    is_string($disk)
                    && config('filesystems.disks.' . $disk)
                ) {
                    return $disk;
                }
            }
        }

        return 'local';
    }

    /**
     * Obtener el directorio de respaldos.
     */
    private function obtenerDirectorioBackup(): string
    {
        $nombre = trim(
            (string) config(
                'backup.backup.name',
                'Laravel'
            )
        );

        return $nombre !== ''
            ? trim($nombre, '/')
            : 'Laravel';
    }

    /**
     * Resolver una ruta absoluta dentro de un disco.
     */
    private function resolverRutaAbsolutaDisco(
        string $disk,
        string $rutaRelativa
    ): string {
        /** @var FilesystemAdapter $storage */
        $storage = Storage::disk($disk);

        return $storage->path($rutaRelativa);
    }

    /**
     * Localizar mariadb-dump o mysqldump.
     */
    private function resolverRutaMySqlDump(): string
    {
        $candidatos = array_filter([
            env('MYSQLDUMP_PATH'),

            '/usr/bin/mariadb-dump',
            '/usr/local/bin/mariadb-dump',

            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',

            PHP_OS_FAMILY === 'Windows'
                ? 'C:\\xampp\\mysql\\bin\\mysqldump.exe'
                : null,

            PHP_OS_FAMILY === 'Windows'
                ? 'C:\\Program Files\\MariaDB 10.11\\bin\\mariadb-dump.exe'
                : null,

            PHP_OS_FAMILY === 'Windows'
                ? 'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe'
                : null,

            'mariadb-dump',
            'mysqldump',
        ]);

        foreach ($candidatos as $candidato) {
            if (
                in_array(
                    $candidato,
                    ['mariadb-dump', 'mysqldump'],
                    true
                )
            ) {
                return $candidato;
            }

            if (
                is_string($candidato)
                && $candidato !== ''
                && file_exists($candidato)
            ) {
                return $candidato;
            }
        }

        throw new RuntimeException(
            'No se encontró mariadb-dump ni mysqldump.'
        );
    }

    /**
     * Generar un nombre de archivo único.
     */
    private function generarNombreBaseUnico(
        string $disk,
        string $directorio
    ): string {
        /** @var FilesystemAdapter $storage */
        $storage = Storage::disk($disk);

        $base = now()->format('Y-m-d-H-i-s');
        $nombre = $base;
        $contador = 1;

        while (
            $storage->exists(
                trim(
                    $directorio . '/' . $nombre . '.zip',
                    '/'
                )
            )
            ||
            $storage->exists(
                trim(
                    $directorio . '/' . $nombre . '.sql',
                    '/'
                )
            )
        ) {
            $nombre = $base . '-' . $contador;
            $contador++;
        }

        return $nombre;
    }

    /**
     * Obtener el archivo de respaldo más reciente.
     */
    private function obtenerBackupMasReciente(): ?array
    {
        $indice = [];

        $discos = config(
            'backup.backup.destination.disks',
            ['local']
        );

        if (!is_array($discos)) {
            $discos = ['local'];
        }

        $nombreBackup = trim(
            (string) config(
                'backup.backup.name',
                'Laravel'
            ),
            '/'
        );

        foreach ($discos as $disco) {
            try {
                if (
                    !is_string($disco)
                    || !config('filesystems.disks.' . $disco)
                ) {
                    continue;
                }

                /** @var FilesystemAdapter $storage */
                $storage = Storage::disk($disco);

                $rutas = array_unique([
                    $nombreBackup,
                    '',
                ]);

                foreach ($rutas as $rutaBase) {
                    $archivos = $rutaBase !== ''
                        ? $storage->allFiles($rutaBase)
                        : $storage->allFiles();

                    foreach ($archivos as $archivo) {
                        if (
                            !preg_match(
                                '/\.(zip|sql|gz)$/i',
                                $archivo
                            )
                        ) {
                            continue;
                        }

                        $clave = $disco . '|' . $archivo;

                        $indice[$clave] = [
                            'disk'           => $disco,
                            'path'           => $archivo,
                            'nombre_archivo' => basename($archivo),

                            'tamano_bytes' => (int) $storage->size(
                                $archivo
                            ),

                            'ultima_modificacion' => (int) $storage
                                ->lastModified($archivo),
                        ];
                    }
                }
            } catch (Throwable $e) {
                Log::warning(
                    'No se pudo inspeccionar el disco de respaldos.',
                    [
                        'disk'    => $disco,
                        'mensaje' => $e->getMessage(),
                    ]
                );
            }
        }

        if (empty($indice)) {
            return null;
        }

        $respaldos = array_values($indice);

        usort(
            $respaldos,
            static function (array $a, array $b): int {
                return (
                    $b['ultima_modificacion'] ?? 0
                ) <=> (
                    $a['ultima_modificacion'] ?? 0
                );
            }
        );

        return $respaldos[0] ?? null;
    }

    /**
     * Obtener el tipo MIME del archivo.
     */
    private function obtenerTipoContenido(
        string $nombreArchivo
    ): string {
        $extension = strtolower(
            pathinfo(
                $nombreArchivo,
                PATHINFO_EXTENSION
            )
        );

        return match ($extension) {
            'zip'   => 'application/zip',
            'sql'   => 'application/sql',
            'gz'    => 'application/gzip',
            default => 'application/octet-stream',
        };
    }

    /**
     * Resolver layout y ruta de regreso según el usuario.
     */
    private function resolverContextoVista(): array
    {
        $user = Auth::user();

        $layout = 'layouts.app-estudiantes';

        $dashboardRoute = Route::has('dashboard')
            ? route('dashboard')
            : url('/dashboard');

        if (session('login_tipo') === 'empleado') {
            $dashboardRoute = Route::has('empleado.dashboard')
                ? route('empleado.dashboard')
                : url('/dashboard');

            $textoRol = $this->obtenerTextoRol($user);

            if (
                $this->contieneTodos(
                    $textoRol,
                    ['secret', 'acad']
                )
            ) {
                $layout = 'layouts.app-secretaria-academica';
            } elseif (
                str_contains($textoRol, 'general')
                && str_contains($textoRol, 'secret')
            ) {
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

    /**
     * Obtener texto relacionado con el rol.
     */
    private function obtenerTextoRol(
        ?Authenticatable $user
    ): string {
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

        $texto = implode(
            ' ',
            array_filter(
                array_map(
                    static function ($valor): string {
                        return is_scalar($valor)
                            ? trim((string) $valor)
                            : '';
                    },
                    $fragmentos
                )
            )
        );

        $texto = mb_strtolower($texto);

        return str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ'],
            ['a', 'e', 'i', 'o', 'u', 'n'],
            $texto
        );
    }

    /**
     * Verificar que un texto contenga todos los fragmentos.
     */
    private function contieneTodos(
        string $texto,
        array $fragmentos
    ): bool {
        foreach ($fragmentos as $fragmento) {
            if (!str_contains($texto, $fragmento)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtener el nombre del usuario autenticado.
     */
    private function obtenerNombreUsuarioActual(): string
    {
        $user = Auth::user();

        if (!$user) {
            return 'Sistema';
        }

        $posiblesNombres = [
            data_get($user, 'persona.nombre_persona'),
            data_get($user, 'nombre_persona'),
            data_get($user, 'name'),
            data_get($user, 'email'),
        ];

        foreach ($posiblesNombres as $nombre) {
            if (
                is_scalar($nombre)
                && trim((string) $nombre) !== ''
            ) {
                return trim((string) $nombre);
            }
        }

        return 'Usuario autenticado';
    }

    /**
     * Formatear el tamaño del archivo.
     */
    private function formatearTamano(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1024 * 1024) {
            return number_format(
                $bytes / 1024,
                2
            ) . ' KB';
        }

        if ($bytes < 1024 * 1024 * 1024) {
            return number_format(
                $bytes / (1024 * 1024),
                2
            ) . ' MB';
        }

        return number_format(
            $bytes / (1024 * 1024 * 1024),
            2
        ) . ' GB';
    }
}