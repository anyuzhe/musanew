<?php

namespace App\Repositories;

use App\Models\CompanyUser;
use App\Models\ExternalToken;
use App\User;

class TokenRepository
{
    protected static $auto_continue = false;
    protected static $auto_continue_time = 28800;

    public static function getToken()
    {
        global $LOGIN_TOKEN_STR;
        if($LOGIN_TOKEN_STR){
            return $LOGIN_TOKEN_STR;
        }
        $request = app('request');
        $token = $request->get('token');
        if(!$token)
            $token = $request->header('token');
        if(!$token)
            $token = $request->header('Token');
        $LOGIN_TOKEN_STR = $token;
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

    public static function setCurrentCompany($company_id, $token=false)
    {
        if(!$token)
            $token = TokenRepository::getTokenModel();
        $token->current_company_id = $company_id;
        $token->save();
    }

    public static function getCurrentCompany()
    {
        global $LOGIN_USER_CURRENT_COMPANY;
        global $LOGIN_TOKEN;
        if($LOGIN_USER_CURRENT_COMPANY)
            return $LOGIN_USER_CURRENT_COMPANY;

        $user = self::getUser();
        if($user){
            if($LOGIN_TOKEN && $LOGIN_TOKEN->company){
                $LOGIN_USER_CURRENT_COMPANY = $LOGIN_TOKEN->company;
                return $LOGIN_USER_CURRENT_COMPANY;
            }
            $LOGIN_USER_CURRENT_COMPANY = $user->company->first();
            if(!$LOGIN_USER_CURRENT_COMPANY){
                $current_company = $user->companies->first();
                if($current_company){
                    self::setCurrentCompany($current_company->id);
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

    public static function getAdmin()
    {
        global $LOGIN_ADMIN;
        if(!$LOGIN_ADMIN){
            $token = TokenRepository::getToken();
            if(!$token)
                return null;
            $LOGIN_ADMIN = User::where('remember_token', $token)->first();
            return $LOGIN_ADMIN;
        }else{
            return $LOGIN_ADMIN;
        }
    }
}
