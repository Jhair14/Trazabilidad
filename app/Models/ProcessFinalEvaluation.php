<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessFinalEvaluation extends Model
{
    protected $table = 'process_final_evaluation';
    protected $primaryKey = 'evaluation_id';
    public $timestamps = false;
    
    protected $fillable = [
        'evaluation_id',
        'batch_id',
        'inspector_id',
        'reason',
        'observations',
        'evaluation_date'
    ];

    protected $casts = [
        'evaluation_date' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'batch_id', 'batch_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'inspector_id', 'operator_id');
    }
}
