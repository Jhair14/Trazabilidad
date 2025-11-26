<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitOfMeasure extends Model
{
    protected $table = 'unit_of_measure';
    protected $primaryKey = 'unit_id';
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
        return $this->hasMany(RawMaterialBase::class, 'unit_id', 'unit_id');
    }
}
