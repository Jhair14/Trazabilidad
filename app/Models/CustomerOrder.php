<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerOrder extends Model
{
    protected $table = 'customer_order';
    protected $primaryKey = 'order_id';
    public $timestamps = false;
    
    protected $fillable = [
        'order_id',
        'customer_id',
        'order_number',
        'name',
        'status',
        'creation_date',
        'delivery_date',
        'priority',
        'description',
        'observations',
        'editable_until',
        'approved_at',
        'approved_by',
        'rejection_reason'
    ];

    protected $casts = [
        'creation_date' => 'date',
        'delivery_date' => 'date',
        'priority' => 'integer',
        'editable_until' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductionBatch::class, 'order_id', 'order_id');
    }

    public function materialRequests(): HasMany
    {
        return $this->hasMany(MaterialRequest::class, 'order_id', 'order_id');
    }

    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class, 'order_id', 'order_id');
    }

    public function destinations(): HasMany
    {
        return $this->hasMany(OrderDestination::class, 'order_id', 'order_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'approved_by', 'operator_id');
    }

    /**
     * Verifica si el pedido puede ser editado o cancelado
     */
    public function canBeEdited(): bool
    {
        if ($this->status !== 'pendiente') {
            return false;
        }

        if ($this->editable_until && now()->greaterThan($this->editable_until)) {
            return false;
        }

        return true;
    }
}
