<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderEnvioTracking extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_envio_tracking';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'destination_id',
        'envio_id',
        'envio_codigo',
        'status',
        'error_message',
        'request_data',
        'response_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
    ];

    /**
     * Get the customer order that owns the tracking record.
     */
    public function order()
    {
        return $this->belongsTo(CustomerOrder::class, 'order_id', 'order_id');
    }

    /**
     * Get the destination that owns the tracking record.
     */
    public function destination()
    {
        return $this->belongsTo(OrderDestination::class, 'destination_id', 'destination_id');
    }

    /**
     * Scope a query to only include successful trackings.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope a query to only include failed trackings.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include pending trackings.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
