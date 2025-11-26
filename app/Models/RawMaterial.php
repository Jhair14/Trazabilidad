<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterial extends Model
{
    protected $table = 'raw_material';
    protected $primaryKey = 'raw_material_id';
    public $timestamps = false;
    
    protected $fillable = [
        'raw_material_id',
        'material_id',
        'supplier_id',
        'supplier_batch',
        'invoice_number',
        'receipt_date',
        'expiration_date',
        'quantity',
        'available_quantity',
        'receipt_conformity',
        'receipt_signature',
        'observations'
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'expiration_date' => 'date',
        'quantity' => 'decimal:4',
        'available_quantity' => 'decimal:4',
        'receipt_conformity' => 'boolean',
    ];

    public function materialBase(): BelongsTo
    {
        return $this->belongsTo(RawMaterialBase::class, 'material_id', 'material_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    public function batchRawMaterials(): HasMany
    {
        return $this->hasMany(BatchRawMaterial::class, 'raw_material_id', 'raw_material_id');
    }
}
