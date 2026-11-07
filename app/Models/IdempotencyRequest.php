<?php

namespace App\Models;

use Database\Factories\IdempotencyRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdempotencyRequest extends Model
{
    /** @use HasFactory<IdempotencyRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'key',
        'request_hash',
        'order_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
