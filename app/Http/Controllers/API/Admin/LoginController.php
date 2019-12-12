<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\ExternalToken;
use App\Models\PasswordFindCode;
use App\User;
use App\Models\UserBasicInfo;
use App\ZL\Moodle\EmailHelper;
use App\ZL\Moodle\TokenHelper;
use Illuminate\Support\Facades\Hash;

class LoginController extends CommonController
{
    public function login()
    {
        $email = $this->request->get('username');
        $password = $this->request->get('password');
        $user = $this->getUserByEmail($email);
        if(!$user)
            return $this->apiReturnJson('2001');
        if(Hash::check($password, $user->password)){
            if(!$token = $user->remember_token){
                $token = md5(uniqid(rand(), 1));
                $user->remember_token = $token;
                $user->save();
            }
            return $this->apiReturnJson(0, ['token'=>$token]);
        }else{
            return $this->apiReturnJson('2001');
        }
    }

    public function logout()
    {
        return $this->apiReturnJson(0);
    }

    protected function getUserByEmail($email)
    {
        $has = User::where([
            ['email',$email],
        ])->first();;
        if($has)
            return $has;

        return User::where('name', $email)->first();
    }

}
