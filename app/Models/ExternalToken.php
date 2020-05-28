<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalToken extends Model
{
    protected $connection = 'moodle';

    public $fillable = [
        'token',
        'privatetoken',
        'tokentype',
        'userid',
        'externalserviceid',
        'sid',
        'contextid',
        'creatorid',
        'iprestriction',
        'validuntil',
        'timecreated',
        'lastaccess',
        'current_company_id',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\Models\User','userid');
    }

    public function userBasicInfo()
    {
        return $this->belongsTo('App\Models\UserBasicInfo','userid');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company','current_company_id');
    }
}
