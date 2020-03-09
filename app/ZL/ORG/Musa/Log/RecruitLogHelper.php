<?php


namespace App\ZL\ORG\Musa\Log;


use App\Models\UserBasicInfo;

class RecruitLogHelper
{

    public static function changeValue($key, $value)
    {
        if($key==='leading_id'){
            $ub = UserBasicInfo::where('user_id',$value)->first();
            if($ub)
                return $ub->realname;
            else
                return '找不到用户';
        }elseif($key==='is_public'){
            if($value)
                return '是';
            else
                return '否';
        }
    }
}
