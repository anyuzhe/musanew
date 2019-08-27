<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordFindCode extends Model
{
    public $table = 'user_password_find_code';
    protected $connection = 'musa';

    public $fillable = [
        'user_id',
        'type',
        'operation',
        'code',
        'status',
    ];
}
