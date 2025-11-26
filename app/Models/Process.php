<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Process extends Model
{
    protected $table = 'process';
    protected $primaryKey = 'process_id';
    public $timestamps = false;
    
    protected $fillable = [
        'process_id',
        'code',
        'name',
        'description',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function processMachines(): HasMany
    {
        return $this->hasMany(ProcessMachine::class, 'process_id', 'process_id');
    }
}

