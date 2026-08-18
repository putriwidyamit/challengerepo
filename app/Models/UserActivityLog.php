<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivityLog extends Model
{
    protected $table = 'user_activity';
    protected $primaryKey = 'activity_id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'activity_type',
        'activity_action',
        'ip_address',
        'user_agent',
        'metadata',
        'activity_timestamp',
    ];

    protected $casts = [
        'user_id' => 'int',
        'metadata' => 'array',
        'activity_timestamp' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(WsUser::class, 'user_id', 'user_id');
    }
}
