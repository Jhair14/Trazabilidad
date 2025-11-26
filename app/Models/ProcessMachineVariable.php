<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessMachineVariable extends Model
{
    protected $table = 'process_machine_variable';
    protected $primaryKey = 'variable_id';
    public $timestamps = false;
    
    protected $fillable = [
        'variable_id',
        'process_machine_id',
        'standard_variable_id',
        'min_value',
        'max_value',
        'target_value',
        'mandatory'
    ];

    protected $casts = [
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'target_value' => 'decimal:2',
        'mandatory' => 'boolean',
    ];

    public function processMachine(): BelongsTo
    {
        return $this->belongsTo(ProcessMachine::class, 'process_machine_id', 'process_machine_id');
    }

    public function standardVariable(): BelongsTo
    {
        return $this->belongsTo(StandardVariable::class, 'standard_variable_id', 'variable_id');
    }
}
