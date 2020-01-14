<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkillCategory extends Model
{
    protected $table = 'skills_category';
    protected $connection = 'musa';
    public $timestamps = false;
    public $fillable = [
        'category_name',
        'pid',
        'sort',
    ];

    public function parent()
    {
        return $this->belongsTo(SkillCategory::class, 'pid');

    }
}
