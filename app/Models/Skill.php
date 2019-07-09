<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $table = 'skills';
    public $timestamps=false;
    public $fillable = [
        'name',
        'category_l1_id',
        'category_l2_id',
    ];
}
