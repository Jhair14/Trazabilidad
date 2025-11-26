<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchRawMaterial extends Model
{
    protected $table = 'batch_raw_material';
    protected $primaryKey = 'batch_material_id';
    public $timestamps = false;
    
    protected $fillable = [
        'batch_material_id',
        'batch_id',
        'raw_material_id',
        'planned_quantity',
        'used_quantity'
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:4',
        'used_quantity' => 'decimal:4',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'batch_id', 'batch_id');
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class, 'raw_material_id', 'raw_material_id');
    }
}
