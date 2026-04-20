<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovementModel extends Model
{
    use HasUuids;

    protected $table = 'stock_movements';

    protected $fillable = [
        'id',
        'variant_id',
        'type',
        'quantity',
        'reason',
        'referenced_movement_id',
    ];

    public function referencedMovement(): BelongsTo
    {
        return $this->belongsTo(self::class, 'referenced_movement_id');
    }
}
