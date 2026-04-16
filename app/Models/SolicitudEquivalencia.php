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
        return DB::select('CALL SEL_SOLICITUDES_EQUIVALENCIA_ALUMNO(?)', [
            $idPersona
        ]);
    }

    public static function solicitudesPendientes(): array
    {
        return DB::select('CALL SEL_SOLICITUDES_EQUIVALENCIA_PENDIENTES()');
    }

    public static function obtenerCabecera(int $idSolicitud): ?object
    {
        $resultado = DB::select('CALL SEL_SOLICITUD_EQUIVALENCIA_CABECERA(?)', [
            $idSolicitud
        ]);

        return $resultado[0] ?? null;
    }

    public static function obtenerDetalle(int $idSolicitud): array
    {
        return DB::select('CALL SEL_DETALLE_SOLICITUD_EQUIVALENCIA(?)', [
            $idSolicitud
        ]);
    }

    public static function obtenerEquivalenciasPreliminares(int $idSolicitud): array
    {
        return DB::select('CALL SEL_EQUIVALENCIAS_PRELIMINARES_SOLICITUD(?)', [
            $idSolicitud
        ]);
    }

    public static function obtenerDocumento(int $idSolicitud): ?object
    {
        $resultado = DB::select('CALL SEL_DOCUMENTO_SOLICITUD_EQUIVALENCIA(?)', [
            $idSolicitud
        ]);

        return $resultado[0] ?? null;
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
