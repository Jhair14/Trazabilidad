<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'product';
    protected $primaryKey = 'product_id';
    public $timestamps = true;
    
    protected $fillable = [
        'code',
        'name',
        'type',
        'weight',
        'unit_id',
        'description',
        'active'
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'unit_id', 'unit_id');
    }

    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class, 'product_id', 'product_id');
    }
}









