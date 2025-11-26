<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    protected $table = 'machine';
    protected $primaryKey = 'machine_id';
    public $timestamps = false;
    
    protected $fillable = [
        'machine_id',
        'code',
        'name',
        'description',
        'image_url',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function operators(): BelongsToMany
    {
        return $this->belongsToMany(Operator::class, 'operator_machine', 'machine_id', 'operator_id');
    }

    public function processMachines(): HasMany
    {
        return $this->hasMany(ProcessMachine::class, 'machine_id', 'machine_id');
    }
}

