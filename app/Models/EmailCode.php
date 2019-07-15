<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailCode extends Model
{
    protected $table = 'emailCode';

    protected $connection = 'musa';

    public $fillable = [
    ];
}
