<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcessMachine extends Model
{
    protected $table = 'process_machine';
    protected $primaryKey = 'process_machine_id';
    public $timestamps = false;
    
    protected $fillable = [
        'process_machine_id',
        'process_id',
        'machine_id',
        'step_order',
        'name',
        'description',
        'estimated_time'
    ];

    protected $casts = [
        'step_order' => 'integer',
        'estimated_time' => 'integer',
    ];

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class, 'process_id', 'process_id');
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class, 'machine_id', 'machine_id');
    }

    public function variables(): HasMany
    {
        return $this->hasMany(ProcessMachineVariable::class, 'process_machine_id', 'process_machine_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(ProcessMachineRecord::class, 'process_machine_id', 'process_machine_id');
    }
}
