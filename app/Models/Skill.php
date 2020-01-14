<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $table = 'skills';
    public $timestamps=false;
    protected $connection = 'musa';
    public $fillable = [
        'name',
        'category_l1_id',
        'category_l2_id',
        'sort',
    ];
}
