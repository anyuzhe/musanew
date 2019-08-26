<?php

namespace App\Repositories;

use App\Models\CompanyUser;
use App\Models\ExternalToken;

class TokenRepository
{
    protected static $auto_continue = true;
    protected static $auto_continue_time = 28800;

    public static function getToken()
    {
        $request = app('request');
        $token = $request->get('token');
        if(!$token)
            $token = $request->header('token');
        if(!$token)
            $token = $request->header('Token');
        return $token;
    }

    public static function getUser()
    {
        global $LOGIN_USER;
        if(!$LOGIN_USER){
            $token = TokenRepository::getTokenModel();
            if(!$token)
                return null;
            $LOGIN_USER = $token->user;
            return $LOGIN_USER;
        }else{
            return $LOGIN_USER;
        }
    }

    public static function getCurrentCompany()
    {
        global $LOGIN_USER_CURRENT_COMPANY;
        if($LOGIN_USER_CURRENT_COMPANY)
            return $LOGIN_USER_CURRENT_COMPANY;

        $user = self::getUser();
        if($user){
            $LOGIN_USER_CURRENT_COMPANY = $user->company->first();
            if(!$LOGIN_USER_CURRENT_COMPANY){
                $current_company = $user->companies->first();
                if($current_company){
                    $r = CompanyUser::where('company_id',$current_company->id)->first();
                    $r->is_current = 1;
                    $r->save();
                    $LOGIN_USER_CURRENT_COMPANY = $current_company;
                }
            }
        }
        return $LOGIN_USER_CURRENT_COMPANY;
    }

    public static function getTokenModel($token = false)
    {
        if(!$token)
            $token = self::getToken();
        global $LOGIN_TOKEN;
        if($LOGIN_TOKEN)
            return $LOGIN_TOKEN;
        $tokenModel = ExternalToken::where('token', $token)->first();
        if(!$tokenModel)
            return null;
        if($tokenModel->validuntil<time()){
            if(self::$auto_continue){
                $tokenModel->validuntil += self::$auto_continue_time;
                $tokenModel->save();
            }else{
                $tokenModel->delete();
                return null;
            }
        }
        $LOGIN_TOKEN = $tokenModel;

        global $LOGIN_USER;
        if(!$LOGIN_USER)
            $LOGIN_USER = $tokenModel->user;
        return $tokenModel;
    }
}
