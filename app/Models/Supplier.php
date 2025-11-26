<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table = 'supplier';
    protected $primaryKey = 'supplier_id';
    public $timestamps = false;
    
    protected $fillable = [
        'supplier_id',
        'business_name',
        'trading_name',
        'tax_id',
        'contact_person',
        'phone',
        'email',
        'address',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function rawMaterials(): HasMany
    {
        return $this->hasMany(RawMaterial::class, 'supplier_id', 'supplier_id');
    }

    public function supplierResponses(): HasMany
    {
        return $this->hasMany(SupplierResponse::class, 'supplier_id', 'supplier_id');
    }
}

