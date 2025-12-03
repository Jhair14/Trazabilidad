<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDestinationProduct extends Model
{
    protected $table = 'order_destination_product';
    protected $primaryKey = 'destination_product_id';
    public $timestamps = true;
    
    protected $fillable = [
        'destination_product_id',
        'destination_id',
        'order_product_id',
        'quantity',
        'observations'
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function destination(): BelongsTo
    {
        return $this->belongsTo(OrderDestination::class, 'destination_id', 'destination_id');
    }

    public function orderProduct(): BelongsTo
    {
        return $this->belongsTo(OrderProduct::class, 'order_product_id', 'order_product_id');
    }
}

