<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Storage extends Model
{
    protected $table = 'storage';
    protected $primaryKey = 'storage_id';
    public $timestamps = false;
    
    protected $fillable = [
        'storage_id',
        'batch_id',
        'location',
        'condition',
        'quantity',
        'observations',
        'pickup_latitude',
        'pickup_longitude',
        'pickup_address',
        'pickup_reference',
        'storage_date',
        'retrieval_date'
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'pickup_latitude' => 'decimal:8',
        'pickup_longitude' => 'decimal:8',
        'storage_date' => 'datetime',
        'retrieval_date' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'batch_id', 'batch_id');
    }
}
