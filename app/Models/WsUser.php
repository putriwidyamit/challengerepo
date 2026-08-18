<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WsUser extends Model
{
    protected $table = 'ws_user';
    protected $primaryKey = 'user_id';
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'user_name',
        'full_name',
        'user_email',
        'msisdn',
        'status',
        'create_time',
        'update_time',
        'last_login',
    ];

    protected $casts = [
        'user_id' => 'int',
        'status' => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'last_login' => 'datetime',
    ];

    /**
     * Mask phone number for API response
     */
    public static function maskPhone($phone)
    {
        if (!$phone || strlen($phone) < 8) {
            return null;
        }

        $start = substr($phone, 0, 4);
        $end = substr($phone, -2);
        return $start . '****' . $end;
    }
}
