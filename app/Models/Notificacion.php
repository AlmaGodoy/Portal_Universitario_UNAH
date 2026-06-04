<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'tbl_notificacion';

    protected $primaryKey = 'id_notificacion';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario_destino',
        'titulo',
        'mensaje',
        'tipo',
        'url_destino',
        'leida',
        'fecha_creacion',
        'fecha_leida',
    ];

    protected $casts = [
        'leida' => 'boolean',
        'fecha_creacion' => 'datetime',
        'fecha_leida' => 'datetime',
    ];

    public function scopeParaUsuario($query, $idUsuario)
    {
        return $query->where('id_usuario_destino', $idUsuario);
    }

    public function scopeNoLeidas($query)
    {
        return $query->where('leida', 0);
    }

    public function scopeRecientes($query)
    {
        return $query->orderByDesc('id_notificacion');
    }

    public function marcarComoLeida()
    {
        if (!$this->leida) {
            $this->update([
                'leida' => 1,
                'fecha_leida' => now(),
            ]);
        }
    }

    public function getIconoAttribute()
    {
        return match ($this->tipo) {
            'success' => 'fas fa-circle-check',
            'warning' => 'fas fa-triangle-exclamation',
            'danger', 'error' => 'fas fa-circle-xmark',
            default => 'fas fa-file-alt',
        };
    }

    public function getColorAttribute()
    {
        return match ($this->tipo) {
            'success' => 'green',
            'warning' => 'gold',
            'danger', 'error' => 'red',
            default => 'blue',
        };
    }
}