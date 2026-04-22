<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
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
     * Laravel usará esta columna como password.
     */
    public function getAuthPassword()
    {
        return $this->contraseña;
    }

    /**
     * RELACIÓN CON PERSONA
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    /**
     * RELACIÓN CON EMPLEADO
     */
    public function empleado()
    {
        return $this->hasOne(Empleado::class, 'id_persona', 'id_persona');
    }

    /**
     * RELACIÓN CON ESTUDIANTE
     */
    public function estudiante()
    {
        return $this->hasOne(Estudiante::class, 'id_persona', 'id_persona');
    }

    /**
     * Laravel suele buscar "email".
     * Aquí lo exponemos desde tbl_persona.correo_institucional.
     */
    public function getEmailAttribute()
    {
        return $this->persona?->correo_institucional;
    }

    /**
     * Correo que se usará para enviar la verificación.
     */
    public function getEmailForVerification()
    {
        return $this->persona?->correo_institucional;
    }

    /**
     * También ayuda a las notificaciones por correo.
     */
    public function routeNotificationForMail($notification = null)
    {
        return $this->persona?->correo_institucional;
    }

    /**
     * Determina si el correo ya fue verificado.
     *
     * Ajuste actual:
     * - estado = 1  => verificado / activo
     * - estado = 0  => no verificado
     *
     * Si tu lógica real es distinta, aquí se cambia.
     */
    public function hasVerifiedEmail()
    {
        return (int) ($this->persona?->estado ?? 0) === 1;
    }

    /**
     * Marca el correo como verificado.
     */
    public function markEmailAsVerified()
    {
        if (!$this->persona) {
            return false;
        }

        return $this->persona->update([
            'estado' => 1,
        ]);
    }

    /**
     * Opcional: para compatibilidad con código que consulte email_verified_at.
     */
    public function getEmailVerifiedAtAttribute()
    {
        return $this->hasVerifiedEmail() ? now() : null;
    }
}