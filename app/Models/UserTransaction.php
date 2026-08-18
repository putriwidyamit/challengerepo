<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTransaction extends Model
{
    protected $table = 'user_transactions';
    protected $primaryKey = 'transaction_id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'order_id',
        'type',
        'amount',
        'payment_method',
        'status',
        'description',
        'transaction_time',
    ];

    protected $casts = [
        'user_id' => 'int',
        'order_id' => 'int',
        'amount' => 'decimal:2',
        'transaction_time' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(WsUser::class, 'user_id', 'user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(UserOrder::class, 'order_id', 'order_id');
    }
}
