<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialRequest extends Model
{
    protected $table = 'material_request';
    protected $primaryKey = 'request_id';
    public $timestamps = false;
    
    protected $fillable = [
        'request_id',
        'order_id',
        'request_number',
        'request_date',
        'required_date',
        'priority',
        'observations'
    ];

    protected $casts = [
        'request_date' => 'date',
        'required_date' => 'date',
        'priority' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class, 'order_id', 'order_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(MaterialRequestDetail::class, 'request_id', 'request_id');
    }

    public function supplierResponses(): HasMany
    {
        return $this->hasMany(SupplierResponse::class, 'request_id', 'request_id');
    }
}
