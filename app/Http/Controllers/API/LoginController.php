<?php

namespace App\Http\Controllers\API;

use App\Models\ExternalToken;
use App\Models\PasswordFindCode;
use App\Models\User;
use App\Models\UserBasicInfo;
use App\ZL\Moodle\EmailHelper;
use App\ZL\Moodle\TokenHelper;

class LoginController extends CommonController
{
    public function test()
    {
        $this->requireMoodleConfig();
        dump($CFG->tokenduration);
        dump(date('Y-m-d H:i;s'));
    }
    public function login()
    {
        $email = $this->request->get('email');
        $password = $this->request->get('password');
        $user = $this->getUserByEmail($email);
        if(!$user)
            return $this->apiReturnJson('2001');
        //引入moodle文件
        $this->requireMoodleConfig();

        $res = validate_internal_user_password($user, $password);
        if($res){
            $token = TokenHelper::generateNewTokenForUser($user);
            ExternalToken::where('id', $token->id)->update(['lastaccess'=>time()]);
            User::where('id', $user->id)->update([
                'lastaccess'=>time(),
            ]);

            return $this->apiReturnJson(0, ['token'=>$token->token]);
        }else{
            return $this->apiReturnJson('2001');
        }
    }

    public function sendCode()
    {
        $email = $this->request->get('email');
        $user = $this->getUserByEmail($email);
        if(!$user){
            return $this->apiReturnJson("9998", null, '邮箱不存在');
        }
        $code = rand(100000, 999999);
        PasswordFindCode::create([
            'type'=>1,
            'userid'=>$user->id,
            'operation'=>3,
            'status'=>0,
            'code'=>$code,
        ]);
        $this->requireMoodleConfig();
        $mailSendRes = EmailHelper::emailToUserCode($user, $code);
        if($mailSendRes){
            return $this->apiReturnJson("0", null, '发送成功');
        }else{
            return $this->apiReturnJson("9999");
        }
    }

    public function register()
    {
        try {
            $this->requireMoodleConfig();
            global $CFG;
            require_once($CFG->dirroot . '/user/editlib.php');
            require_once($CFG->libdir . '/authlib.php');
            require_once('./../login/lib.php');

            $user = $this->request->all();
            $user = json_decode(json_encode($user));
            //邮箱唯一性判断
            $userHas = $this->getUserByEmail($user->email);
            if($userHas){
                return $this->apiReturnJson("9998", null, '邮箱不能重复');
            }
            $user->username = $user->email;
            $user = signup_setup_new_user($user);
            $this->userSignup($user, true);

            User::where('id', $user->id)->update(['confirmed'=>1]);
            UserBasicInfo::create(['user_id'=>$user->id]);
            $user = User::find($user->id);
            $token = TokenHelper::getTokenForUser($user);
            $user->token = $token->token;

            return $this->apiReturnJson(0, $user);
        }catch (\Exception $e) {
            $message = $e->getMessage();
            if($message=="Tried to send you an email but failed!"){
                //注册成功 但是发送邮箱失败
                return $this->apiReturnJson(0, $user);
            }else{
                return $this->apiReturnJson("9999", null, $message);
            }
        }
    }

    public function editPassword()
    {
        $type = $this->request->get('type');
        $code = $this->request->get('code');
        $email = $this->request->get('email');
        $password = $this->request->get('password');
        $user = $this->getUserByEmail($email);
        if(!$user) {
            return $this->apiReturnJson('9998');
        }
        $codeHas = PasswordFindCode::where([
            ['userid',$user->id],
            ['type',1],
            ['operation',3],
            ['status',0],
            ['code',$code],
        ])->first();
        if(!$codeHas){
            return $this->apiReturnJson('2002');
        }
        //修改密码
        $this->requireMoodleConfig();

        $userauth = get_auth_plugin($user->auth);
        if (!$userauth->user_update_password($user, $password)) {
            return $this->apiReturnJson('9999');
        }else{
            require_once('./../user/lib.php');
            global $CFG;
            $CFG->passwordreuselimit = 10;
            user_add_password_history($user->id, $password);
            PasswordFindCode::where('id', $codeHas->id)->update(['status'=>1]);
            return $this->apiReturnJson('0');
        }
    }

    protected function getUserByEmail($email)
    {
        return User::where([
            ['deleted',0],
            ['confirmed',1],
            ['email',$email],
        ])->whereIn('auth', ['manual','email'])->first();
    }


    protected function userSignup(&$user, $notify=true, $confirmationurl = null) {
        global $CFG, $DB, $SESSION;
        require_once($CFG->dirroot.'/user/profile/lib.php');
        require_once($CFG->dirroot.'/user/lib.php');

        $plainpassword = $user->password;
        $user->password = hash_internal_user_password($user->password);
        if (empty($user->calendartype)) {
            $user->calendartype = $CFG->calendartype;
        }

        $user->id = user_create_user($user, false, false);

        user_add_password_history($user->id, $plainpassword);

        // Save any custom profile field information.
        profile_save_data($user);

        // Save wantsurl against user's profile, so we can return them there upon confirmation.
        if (!empty($SESSION->wantsurl)) {
            set_user_preference('auth_email_wantsurl', $SESSION->wantsurl, $user);
        }

        // Trigger event.
        \core\event\user_created::create_from_userid($user->id)->trigger();
        ##发送确认邮箱
//        if (! send_confirmation_email($user, $confirmationurl)) {
//            print_error('auth_emailnoemail', 'auth_email');
//        }

        if ($notify) {
            return true;
        } else {
            return true;
        }
    }
}
