<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $table = 'customer';
    protected $primaryKey = 'customer_id';
    public $timestamps = false;
    
    protected $fillable = [
        'customer_id',
        'business_name',
        'trading_name',
        'tax_id',
        'address',
        'phone',
        'email',
        'contact_person',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(CustomerOrder::class, 'customer_id', 'customer_id');
    }
}
