<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessMachineRecord extends Model
{
    protected $table = 'process_machine_record';
    protected $primaryKey = 'record_id';
    public $timestamps = false;
    
    protected $fillable = [
        'record_id',
        'batch_id',
        'process_machine_id',
        'operator_id',
        'entered_variables',
        'meets_standard',
        'observations',
        'start_time',
        'end_time',
        'record_date'
    ];

    protected $casts = [
        'entered_variables' => 'array',
        'meets_standard' => 'boolean',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'record_date' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'batch_id', 'batch_id');
    }

    public function processMachine(): BelongsTo
    {
        return $this->belongsTo(ProcessMachine::class, 'process_machine_id', 'process_machine_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'operator_id', 'operator_id');
    }
}
