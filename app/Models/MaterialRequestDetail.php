<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialRequestDetail extends Model
{
    protected $table = 'material_request_detail';
    protected $primaryKey = 'detail_id';
    public $timestamps = false;
    
    protected $fillable = [
        'detail_id',
        'request_id',
        'material_id',
        'requested_quantity',
        'approved_quantity'
    ];

    protected $casts = [
        'requested_quantity' => 'decimal:4',
        'approved_quantity' => 'decimal:4',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(MaterialRequest::class, 'request_id', 'request_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(RawMaterialBase::class, 'material_id', 'material_id');
    }
}
