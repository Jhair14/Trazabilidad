<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Spatie\Permission\Traits\HasRoles;

class Operator extends Authenticatable implements JWTSubject
{
    // use HasRoles;
    
    // Map to new English database table
    protected $table = 'operator';
    protected $primaryKey = 'operator_id';
    public $timestamps = false;
    
    protected $fillable = [
        'operator_id',
        'role_id',
        'first_name',
        'last_name',
        'username',
        'password_hash',
        'email',
        'active',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'operator_id' => 'integer',
        'role_id' => 'integer',
        'active' => 'boolean',
    ];

    // Relationship to operator role
    public function role(): BelongsTo
    {
        return $this->belongsTo(OperatorRole::class, 'role_id', 'role_id');
    }

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

    /**
     * Check if the operator has a specific permission
     */
    public function hasPermissionTo(string $permission): bool
    {
        if (!$this->role) {
            return false;
        }

        $roleCode = strtoupper($this->role->code);

        // Admin has all permissions
        if ($roleCode === 'ADMIN') {
            return true;
        }

        // Define permissions per role
        $permissions = [
            'OPERATOR' => [
                'ver panel control',
                'ver materia prima',
                'solicitar materia prima',
                'recepcionar materia prima',
                'gestionar proveedores',
                'gestionar lotes',
                'gestionar maquinas',
                'gestionar procesos',
                'gestionar variables estandar',
                'certificar lotes',
                'ver certificados',
                'almacenar lotes',
                'gestionar pedidos',
                'aprobar pedidos',
                'rechazar pedidos',
            ],
            'CLIENTE' => [
                'ver panel cliente',
                'crear pedidos',
                'ver mis pedidos',
                'editar mis pedidos',
                'cancelar mis pedidos',
                'ver certificados',
            ],
        ];

        $rolePermissions = $permissions[$roleCode] ?? [];

        return in_array($permission, $rolePermissions);
    }
}

