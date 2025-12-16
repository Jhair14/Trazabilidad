<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialMovementLog extends Model
{
    protected $table = 'registro_movimiento_material';
    protected $primaryKey = 'registro_id';
    public $timestamps = false;
    
    protected $fillable = [
        'registro_id',
        'material_id',
        'tipo_movimiento_id',
        'operador_id',
        'cantidad',
        'saldo_anterior',
        'saldo_nuevo',
        'descripcion',
        'observaciones',
        'fecha_movimiento'
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'saldo_anterior' => 'decimal:4',
        'saldo_nuevo' => 'decimal:4',
        'fecha_movimiento' => 'datetime',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(RawMaterialBase::class, 'material_id', 'material_id');
    }

    public function movementType(): BelongsTo
    {
        return $this->belongsTo(MovementType::class, 'tipo_movimiento_id', 'tipo_movimiento_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'operador_id', 'operador_id');
    }
}
