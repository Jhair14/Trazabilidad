<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterialBase extends Model
{
    protected $table = 'raw_material_base';
    protected $primaryKey = 'material_id';
    public $timestamps = false;
    
    protected $fillable = [
        'material_id',
        'category_id',
        'unit_id',
        'code',
        'name',
        'description',
        'available_quantity',
        'minimum_stock',
        'maximum_stock',
        'active'
    ];

    protected $casts = [
        'available_quantity' => 'decimal:4',
        'minimum_stock' => 'decimal:4',
        'maximum_stock' => 'decimal:4',
        'active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(RawMaterialCategory::class, 'category_id', 'category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'unit_id', 'unit_id');
    }

    public function rawMaterials(): HasMany
    {
        return $this->hasMany(RawMaterial::class, 'material_id', 'material_id');
    }

    public function batchRawMaterials(): HasMany
    {
        return $this->hasMany(BatchRawMaterial::class, 'material_id', 'material_id');
    }

    public function movementLogs(): HasMany
    {
        return $this->hasMany(MaterialMovementLog::class, 'material_id', 'material_id');
    }
}
