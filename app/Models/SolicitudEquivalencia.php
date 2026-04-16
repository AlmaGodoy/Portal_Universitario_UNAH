<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SolicitudEquivalencia extends Model
{
    protected $table = 'tbl_solicitud_equivalencia';
    protected $primaryKey = 'id_solicitud_equivalencia';
    public $timestamps = false;

    public static function misSolicitudesPorPersona(int $idPersona): array
    {
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare('CALL SEL_SOLICITUDES_EQUIVALENCIA_ALUMNO(?)');
        $stmt->execute([$idPersona]);

        $resultado = $stmt->fetchAll(\PDO::FETCH_OBJ);

        while ($stmt->nextRowset()) {
            // Consumir result sets pendientes
        }

        $stmt->closeCursor();

        return $resultado;
    }

    public static function solicitudesPendientes(): array
    {
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare('CALL SEL_SOLICITUDES_EQUIVALENCIA_PENDIENTES()');
        $stmt->execute();

        $resultado = $stmt->fetchAll(\PDO::FETCH_OBJ);

        while ($stmt->nextRowset()) {
            //
        }

        $stmt->closeCursor();

        return $resultado;
    }

    public static function obtenerCabecera(int $idSolicitud): ?object
    {
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare('CALL SEL_SOLICITUD_EQUIVALENCIA_CABECERA(?)');
        $stmt->execute([$idSolicitud]);

        $resultado = $stmt->fetchAll(\PDO::FETCH_OBJ);

        while ($stmt->nextRowset()) {
            //
        }

        $stmt->closeCursor();

        return $resultado[0] ?? null;
    }

    public static function obtenerDetalle(int $idSolicitud): array
    {
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare('CALL SEL_DETALLE_SOLICITUD_EQUIVALENCIA(?)');
        $stmt->execute([$idSolicitud]);

        $resultado = $stmt->fetchAll(\PDO::FETCH_OBJ);

        while ($stmt->nextRowset()) {
            //
        }

        $stmt->closeCursor();

        return $resultado;
    }

    public static function obtenerEquivalenciasPreliminares(int $idSolicitud): array
    {
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare('CALL SEL_EQUIVALENCIAS_PRELIMINARES_SOLICITUD(?)');
        $stmt->execute([$idSolicitud]);

        $resultado = $stmt->fetchAll(\PDO::FETCH_OBJ);

        while ($stmt->nextRowset()) {
            //
        }

        $stmt->closeCursor();

        return $resultado;
    }

    public static function obtenerDocumento(int $idSolicitud): ?object
    {
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare('CALL SEL_DOCUMENTO_SOLICITUD_EQUIVALENCIA(?)');
        $stmt->execute([$idSolicitud]);

        $resultado = $stmt->fetchAll(\PDO::FETCH_OBJ);

        while ($stmt->nextRowset()) {
            //
        }

        $stmt->closeCursor();

        return $resultado[0] ?? null;
    }

    public static function obtenerAsignaturasPlanViejo(int $versionPlanViejo): array
    {
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare('CALL SEL_ASIGNATURAS_PLAN_VIEJO(?)');
        $stmt->execute([$versionPlanViejo]);

        $resultado = $stmt->fetchAll(\PDO::FETCH_OBJ);

        while ($stmt->nextRowset()) {
            //
        }

        $stmt->closeCursor();

        return $resultado;
    }

    public static function guardarDetalleSolicitud(int $idSolicitud, int $versionPlanViejo, array $asignaturas): void
    {
        $pdo = DB::connection()->getPdo();

        foreach ($asignaturas as $asignatura) {
            $codigo = trim((string) ($asignatura['codigo_asignatura_viejo'] ?? ''));

            if ($codigo === '') {
                continue;
            }

            $seleccionada = array_key_exists('seleccionada_alumno', $asignatura)
                ? (int) ((bool) $asignatura['seleccionada_alumno'])
                : 1;

            $stmt = $pdo->prepare('CALL INS_SOLICITUD_EQUIVALENCIA_DETALLE(?, ?, ?, ?, ?)');
            $stmt->execute([
                $idSolicitud,
                $versionPlanViejo,
                $codigo,
                null,
                $seleccionada,
            ]);

            while ($stmt->nextRowset()) {
                //
            }

            $stmt->closeCursor();
        }
    }

    public static function validarDetalleSolicitud(
        int $idSolicitud,
        int $versionPlanViejo,
        string $codigoAsignaturaViejo,
        int $validadaRevisor,
        ?string $observacionRevision
    ): void {
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare('CALL UPD_VALIDACION_SOLICITUD_EQUIVALENCIA_DETALLE(?, ?, ?, ?, ?)');
        $stmt->execute([
            $idSolicitud,
            $versionPlanViejo,
            $codigoAsignaturaViejo,
            $validadaRevisor,
            $observacionRevision,
        ]);

        while ($stmt->nextRowset()) {
            //
        }

        $stmt->closeCursor();
    }

    public static function validarSolicitud(
        int $idSolicitud,
        string $estado,
        ?string $observacionRevisor,
        ?int $idUsuarioRevisor
    ): void {
        $pdo = DB::connection()->getPdo();
        $stmt = $pdo->prepare('CALL UPD_VALIDACION_SOLICITUD_EQUIVALENCIA(?, ?, ?, ?)');
        $stmt->execute([
            $idSolicitud,
            $estado,
            $observacionRevisor,
            $idUsuarioRevisor,
        ]);

        while ($stmt->nextRowset()) {
            //
        }

        $stmt->closeCursor();
    }

    public static function esPropietario(int $idSolicitud, int $idPersona): bool
    {
        $cabecera = self::obtenerCabecera($idSolicitud);

        if (!$cabecera) {
            return false;
        }

        return (int) ($cabecera->id_persona ?? 0) === $idPersona;
    }

    public static function puedeAcceder(?object $cabecera, ?string $rol, ?int $idPersona): bool
    {
        if (!$cabecera) {
            return false;
        }

        if (self::puedeRevisarEquivalencias($rol)) {
            return true;
        }

        if (!$idPersona) {
            return false;
        }

        return (int) ($cabecera->id_persona ?? 0) === $idPersona;
    }

    public static function puedeRevisarEquivalencias(?string $rol): bool
    {
        if (!$rol) {
            return false;
        }

        return in_array($rol, [
            'secretaria',
            'secretaria_academica',
            'secretaria_carrera',
            'coordinador',
            'coordinadora',
        ]);
    }

    public static function esSecretaria(?string $rol): bool
    {
        if (!$rol) {
            return false;
        }

        return in_array($rol, [
            'secretaria',
            'secretaria_academica',
            'secretaria_carrera',
        ]);
    }

    public static function esCoordinacion(?string $rol): bool
    {
        if (!$rol) {
            return false;
        }

        return in_array($rol, [
            'coordinador',
            'coordinadora',
        ]);
    }
}