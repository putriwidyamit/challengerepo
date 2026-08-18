<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserOrder extends Model
{
    protected $table = 'user_orders';
    protected $primaryKey = 'order_id';
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'total_amount',
    ];

    protected $casts = [
        'user_id' => 'int',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(WsUser::class, 'user_id', 'user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(UserTransaction::class, 'order_id', 'order_id');
    }
}
