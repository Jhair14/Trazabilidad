<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerOrder extends Model
{
    protected $table = 'pedido_cliente';
    protected $primaryKey = 'pedido_id';
    public $timestamps = false;
    
    protected $fillable = [
        'pedido_id',
        'cliente_id',
        'numero_pedido',
        'nombre',
        'estado',
        'fecha_creacion',
        'fecha_entrega',
        'descripcion',
        'observaciones',
        'editable_hasta',
        'aprobado_en',
        'aprobado_por',
        'razon_rechazo'
    ];

    protected $casts = [
        'fecha_creacion' => 'date',
        'fecha_entrega' => 'date',
        'editable_hasta' => 'datetime',
        'aprobado_en' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'cliente_id', 'cliente_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductionBatch::class, 'pedido_id', 'pedido_id');
    }

    public function materialRequests(): HasMany
    {
        return $this->hasMany(MaterialRequest::class, 'pedido_id', 'pedido_id');
    }

    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class, 'pedido_id', 'pedido_id');
    }

    public function destinations(): HasMany
    {
        return $this->hasMany(OrderDestination::class, 'pedido_id', 'pedido_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'aprobado_por', 'operador_id');
    }

    /**
     * Verifica si el pedido puede ser editado o cancelado
     */
    public function canBeEdited(): bool
    {
        if ($this->estado !== 'pendiente') {
            return false;
        }

        if ($this->editable_hasta && now()->greaterThan($this->editable_hasta)) {
            return false;
        }

        return true;
    }
}
