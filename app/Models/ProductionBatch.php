<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductionBatch extends Model
{
    protected $table = 'production_batch';
    protected $primaryKey = 'batch_id';
    public $timestamps = false;
    
    protected $fillable = [
        'batch_id',
        'order_id',
        'batch_code',
        'name',
        'creation_date',
        'start_time',
        'end_time',
        'target_quantity',
        'produced_quantity',
        'observations'
    ];

    protected $casts = [
        'creation_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'target_quantity' => 'decimal:4',
        'produced_quantity' => 'decimal:4',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class, 'order_id', 'order_id');
    }

    public function rawMaterials(): HasMany
    {
        return $this->hasMany(BatchRawMaterial::class, 'batch_id', 'batch_id');
    }

    public function processMachineRecords(): HasMany
    {
        return $this->hasMany(ProcessMachineRecord::class, 'batch_id', 'batch_id');
    }

    public function finalEvaluation(): HasMany
    {
        return $this->hasMany(ProcessFinalEvaluation::class, 'batch_id', 'batch_id');
    }

    public function latestFinalEvaluation(): HasOne
    {
        return $this->hasOne(ProcessFinalEvaluation::class, 'batch_id', 'batch_id')
            ->latest('evaluation_date');
    }

    public function storage(): HasMany
    {
        return $this->hasMany(Storage::class, 'batch_id', 'batch_id');
    }
}
