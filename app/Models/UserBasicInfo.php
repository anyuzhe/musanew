<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBasicInfo extends Model
{
    protected $table = 'user_basic_info';
    protected $connection = 'moodle';

    public $fillable = [
        'user_id',
        'email',
        'realname',
        'idcard_no',
        'idcard_photo_face',
        'idcard_photo_back',
        'gender',
        'birthdate',
        'is_married',
        'permanent_province_id',
        'permanent_city_id',
        'permanent_district_id',
        'residence_province_id',
        'residence_city_id',
        'residence_district_id',
        'residence_address',
        'job_startime',
        'education',
        'graduate_institutions',
        'major',
        'self_evaluation',
        'avatar',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
