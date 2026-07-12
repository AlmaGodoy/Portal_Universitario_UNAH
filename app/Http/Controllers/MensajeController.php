<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mensaje extends Model
{
    protected $table = 'tbl_mensajes';

    protected $primaryKey = 'id_mensaje';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'id_remitente',
        'id_destinatario',
        'id_mensaje_padre',
        'asunto',
        'contenido',
        'leido',
        'fecha_lectura',
        'eliminado_remitente',
        'eliminado_destinatario',
    ];

    protected $casts = [
        'id_remitente' => 'integer',
        'id_destinatario' => 'integer',
        'id_mensaje_padre' => 'integer',
        'leido' => 'boolean',
        'fecha_lectura' => 'datetime',
        'eliminado_remitente' => 'boolean',
        'eliminado_destinatario' => 'boolean',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function remitente(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_remitente',
            'id_usuario'
        );
    }

    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_destinatario',
            'id_usuario'
        );
    }

    public function mensajePadre(): BelongsTo
    {
        return $this->belongsTo(
            Mensaje::class,
            'id_mensaje_padre',
            'id_mensaje'
        );
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(
            Mensaje::class,
            'id_mensaje_padre',
            'id_mensaje'
        )->orderBy('fecha_creacion', 'asc');
    }

    public function marcarComoLeido(): void
    {
        if (!$this->leido) {
            $this->update([
                'leido' => true,
                'fecha_lectura' => now(),
            ]);
        }
    }
}