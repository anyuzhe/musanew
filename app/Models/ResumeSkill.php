<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeSkill extends Model
{
    protected $table = 'resume_skill';
    public $timestamps = false;
    public $fillable = [
        'resume_id',
        'skill_id',//
        'used_time',//使用时长
        'skill_level',//掌握程度
        'status',//-1:已删除，0：不展示，1展示
    ];
}
