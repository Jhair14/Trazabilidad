<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StandardVariable extends Model
{
    protected $table = 'standard_variable';
    protected $primaryKey = 'variable_id';
    public $timestamps = false;
    
    protected $fillable = [
        'variable_id',
        'code',
        'name',
        'unit',
        'description',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function processMachineVariables(): HasMany
    {
        return $this->hasMany(ProcessMachineVariable::class, 'standard_variable_id', 'variable_id');
    }
}
