<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notificacion extends Model
{
    use HasFactory;

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

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeParaUsuario($query, $idUsuario)
    {
        return $query->where('id_usuario_destino', $idUsuario);
    }

    public function scopeNoLeidas($query)
    {
        return $query->where('leida', 0);
    }

    public function scopeLeidas($query)
    {
        return $query->where('leida', 1);
    }

    public function scopeRecientes($query)
    {
        return $query->orderByDesc('fecha_creacion')
                     ->orderByDesc('id_notificacion');
    }

    /*
    |--------------------------------------------------------------------------
    | Métodos auxiliares
    |--------------------------------------------------------------------------
    */

    public function marcarComoLeida()
    {
        if (!$this->leida) {
            $this->update([
                'leida' => 1,
                'fecha_leida' => now(),
            ]);
        }

        return $this;
    }

    public function marcarComoNoLeida()
    {
        $this->update([
            'leida' => 0,
            'fecha_leida' => null,
        ]);

        return $this;
    }

    public function getIconoAttribute()
    {
        return match ($this->tipo) {
            'success' => 'fas fa-circle-check',
            'warning' => 'fas fa-triangle-exclamation',
            'danger'  => 'fas fa-circle-xmark',
            'error'   => 'fas fa-circle-xmark',
            'info'    => 'fas fa-file-alt',
            default   => 'fas fa-bell',
        };
    }

    public function getColorAttribute()
    {
        return match ($this->tipo) {
            'success' => 'green',
            'warning' => 'gold',
            'danger'  => 'red',
            'error'   => 'red',
            'info'    => 'blue',
            default   => 'blue',
        };
    }
}