<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Operator extends Authenticatable implements JWTSubject
{
    use HasRoles;
    
    // Tabla en español
    protected $table = 'operador';
    protected $primaryKey = 'operador_id';
    public $timestamps = false;
    
    protected $fillable = [
        'operador_id',
        'nombre',
        'apellido',
        'usuario',
        'password_hash',
        'email',
        'activo',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'operador_id' => 'integer',
        'activo' => 'boolean',
    ];

    // Guard name para Spatie Permission
    protected $guard_name = 'web';

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getAuthPassword()
    {
        return $this->attributes['password_hash'] ?? null;
    }
}

