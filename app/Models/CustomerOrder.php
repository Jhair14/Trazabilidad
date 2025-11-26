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
        'creation_date',
        'delivery_date',
        'priority',
        'description',
        'observations'
    ];

    protected $casts = [
        'creation_date' => 'date',
        'delivery_date' => 'date',
        'priority' => 'integer',
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
}
