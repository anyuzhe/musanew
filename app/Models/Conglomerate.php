<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conglomerate extends Model
{
    protected $table = 'conglomerates';

    public $connection = 'musa';

    public $fillable = [
        'name',
    ];

    public function companies()
    {
        return $this->hasMany(Company::class);
    }
}
