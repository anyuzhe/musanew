<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordFindCode extends Model
{
    public $timestamps = false;
    public $table = 'user_password_find_code';

    public $fillable = [
        'userid',
        'type',
        'operation',
        'code',
        'status',
    ];
}
