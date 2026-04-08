<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'tbl_usuario';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'id_persona',
        'contraseña',
        'estado_cuenta',
        'id_rol',
    ];

    protected $hidden = [
        'contraseña',
        'remember_token',
    ];

    /**
     * CONFIGURACIÓN DE LOGIN
     * Indica que la columna de password se llama 'contraseña'
     */
    public function getAuthPassword()
    {
        return $this->contraseña;
    }

    /**
     * RELACIÓN CON PERSONA
     * Para jalar el nombre real desde tbl_persona
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    /**
     * RELACIÓN CON EMPLEADO
     * Conecta al usuario con su cargo usando el id_persona como puente
     */
    public function empleado()
    {
        return $this->hasOne(Empleado::class, 'id_persona', 'id_persona');
    }

    /**
     * RELACIÓN CON ESTUDIANTE
     * Obtiene el registro del estudiante usando id_persona
     */
    public function estudiante()
    {
        return $this->hasOne(Estudiante::class, 'id_persona', 'id_persona');
    }
}