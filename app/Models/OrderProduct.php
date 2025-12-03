<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderProduct extends Model
{
    protected $table = 'order_product';
    protected $primaryKey = 'order_product_id';
    public $timestamps = true;
    
    protected $fillable = [
        'order_product_id',
        'order_id',
        'product_id',
        'quantity',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'observations'
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'approved_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class, 'order_id', 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'approved_by', 'operator_id');
    }

    public function destinationProducts(): HasMany
    {
        return $this->hasMany(OrderDestinationProduct::class, 'order_product_id', 'order_product_id');
    }
}

