<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderDestination extends Model
{
    protected $table = 'order_destination';
    protected $primaryKey = 'destination_id';
    public $timestamps = true;
    
    protected $fillable = [
        'destination_id',
        'order_id',
        'address',
        'reference',
        'latitude',
        'longitude',
        'contact_name',
        'contact_phone',
        'delivery_instructions'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class, 'order_id', 'order_id');
    }

    public function destinationProducts(): HasMany
    {
        return $this->hasMany(OrderDestinationProduct::class, 'destination_id', 'destination_id');
    }
}

