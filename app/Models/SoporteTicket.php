<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SoporteTicket extends Model
{
    protected $table = 'tbl_soporte';
    public $timestamps = false;
    protected $guarded = [];

    /**
     * Tickets base de soporte.
     */
    public function obtenerTicketsPorEmpleado(?int $idPersonaEmpleado = null): Collection
    {
        return collect([
            [
                'id_soporte' => 1,
                'codigo' => 'ST-2026-001',
                'asunto' => 'No puedo iniciar sesión',
                'usuario' => 'María Fernanda López',
                'correo' => 'maria.lopez@unah.edu.hn',
                'carrera' => 'Informática Administrativa',
                'tipo' => 'Acceso al sistema',
                'tipo_key' => 'acceso',
                'prioridad' => 'Alta',
                'prioridad_key' => 'alta',
                'estado' => 'Pendiente',
                'estado_key' => 'pendiente',
                'canal' => 'Correo institucional',
                'fecha' => '2026-04-10 08:20',
                'descripcion' => 'La usuaria no puede ingresar al portal institucional y el sistema indica credenciales inválidas.',
                'solucion_sugerida' => 'Validar correo institucional, revisar si la cuenta está bloqueada y confirmar si necesita restablecimiento de contraseña.',
                'modulo' => 'Panel institucional',
                'id_persona_solicitante' => null,
            ],
            [
                'id_soporte' => 2,
                'codigo' => 'ST-2026-002',
                'asunto' => 'No me deja subir documentos',
                'usuario' => 'Carlos Eduardo Pineda',
                'correo' => 'carlos.pineda@unah.edu.hn',
                'carrera' => 'Informática Administrativa',
                'tipo' => 'Problema con documentos',
                'tipo_key' => 'documentos',
                'prioridad' => 'Media',
                'prioridad_key' => 'media',
                'estado' => 'En proceso',
                'estado_key' => 'en_proceso',
                'canal' => 'Presencial',
                'fecha' => '2026-04-10 09:45',
                'descripcion' => 'El estudiante intenta subir su historial académico, pero el archivo PDF no se refleja en el sistema.',
                'solucion_sugerida' => 'Verificar tamaño y formato del archivo, revisar permisos de carga y confirmar ruta de almacenamiento.',
                'modulo' => 'Mis trámites',
                'id_persona_solicitante' => null,
            ],
            [
                'id_soporte' => 3,
                'codigo' => 'ST-2026-003',
                'asunto' => 'Error al enviar trámite',
                'usuario' => 'Ana Sofía Castellanos',
                'correo' => 'ana.castellanos@unah.edu.hn',
                'carrera' => 'Informática Administrativa',
                'tipo' => 'Problema con trámite',
                'tipo_key' => 'tramite',
                'prioridad' => 'Alta',
                'prioridad_key' => 'alta',
                'estado' => 'Pendiente',
                'estado_key' => 'pendiente',
                'canal' => 'WhatsApp institucional',
                'fecha' => '2026-04-10 10:30',
                'descripcion' => 'Al intentar enviar una solicitud de cambio de carrera, el sistema muestra un error inesperado.',
                'solucion_sugerida' => 'Validar datos obligatorios, revisar respuesta del backend y confirmar si el calendario académico está vigente.',
                'modulo' => 'Mis trámites',
                'id_persona_solicitante' => null,
            ],
            [
                'id_soporte' => 4,
                'codigo' => 'ST-2026-004',
                'asunto' => 'No puedo actualizar perfil',
                'usuario' => 'José Manuel Rivera',
                'correo' => 'jose.rivera@unah.edu.hn',
                'carrera' => 'Informática Administrativa',
                'tipo' => 'Consulta general',
                'tipo_key' => 'consulta',
                'prioridad' => 'Baja',
                'prioridad_key' => 'baja',
                'estado' => 'Resuelto',
                'estado_key' => 'resuelto',
                'canal' => 'Correo institucional',
                'fecha' => '2026-04-09 15:10',
                'descripcion' => 'El usuario reportó que no podía actualizar su número de teléfono en el perfil.',
                'solucion_sugerida' => 'Confirmar permisos de edición y verificar validaciones del formulario.',
                'modulo' => 'Configuración',
                'id_persona_solicitante' => null,
            ],
            [
                'id_soporte' => 5,
                'codigo' => 'ST-2026-005',
                'asunto' => 'No aparece mi resolución',
                'usuario' => 'Paola Andrea Mejía',
                'correo' => 'paola.mejia@unah.edu.hn',
                'carrera' => 'Informática Administrativa',
                'tipo' => 'Problema con documentos',
                'tipo_key' => 'documentos',
                'prioridad' => 'Media',
                'prioridad_key' => 'media',
                'estado' => 'En proceso',
                'estado_key' => 'en_proceso',
                'canal' => 'Presencial',
                'fecha' => '2026-04-09 13:25',
                'descripcion' => 'La estudiante indica que el documento de resolución no aparece disponible para descargar.',
                'solucion_sugerida' => 'Revisar generación del archivo PDF, nombre de archivo, almacenamiento y enlace de descarga.',
                'modulo' => 'Mis trámites',
                'id_persona_solicitante' => null,
            ],
            [
                'id_soporte' => 6,
                'codigo' => 'ST-2026-006',
                'asunto' => 'Consulta sobre uso del portal',
                'usuario' => 'Kevin Alejandro Torres',
                'correo' => 'kevin.torres@unah.edu.hn',
                'carrera' => 'Informática Administrativa',
                'tipo' => 'Consulta general',
                'tipo_key' => 'consulta',
                'prioridad' => 'Baja',
                'prioridad_key' => 'baja',
                'estado' => 'Pendiente',
                'estado_key' => 'pendiente',
                'canal' => 'Llamada telefónica',
                'fecha' => '2026-04-08 11:00',
                'descripcion' => 'El usuario desea orientación sobre el proceso correcto para iniciar un trámite en el portal.',
                'solucion_sugerida' => 'Brindar guía paso a paso, validar requisitos y compartir ruta del módulo correcto.',
                'modulo' => 'Otro',
                'id_persona_solicitante' => null,
            ],
        ])->values();
    }

    /**
     * Resumen general.
     */
    public function obtenerResumen(Collection $tickets): array
    {
        return [
            'total' => $tickets->count(),
            'pendientes' => $tickets->where('estado_key', 'pendiente')->count(),
            'en_proceso' => $tickets->where('estado_key', 'en_proceso')->count(),
            'resueltos' => $tickets->where('estado_key', 'resuelto')->count(),
        ];
    }
}