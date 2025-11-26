<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterialCategory extends Model
{
    protected $table = 'raw_material_category';
    protected $primaryKey = 'category_id';
    public $timestamps = false;
    
    protected $fillable = [
        'code',
        'name',
        'description',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function rawMaterialBases(): HasMany
    {
        return $this->hasMany(RawMaterialBase::class, 'category_id', 'category_id');
    }
}
