<?php

namespace App\Http\Controllers\API;

use App\Models\ExternalToken;
use App\Models\PasswordFindCode;
use App\Models\User;
use App\Models\UserBasicInfo;
use App\Repositories\TokenRepository;
use App\ZL\Moodle\EmailHelper;
use App\ZL\Moodle\TokenHelper;

class LoginController extends CommonController
{
    public function test()
    {
        $this->requireMoodleConfig();
//        dump($CFG->tokenduration);
//        dump(date('Y-m-d H:i;s'));
    }

    public function skipCourse()
    {
        $course_id = $this->request->get('course_id');
        $user = $this->getUser();
        $token = $this->getToken();
        $httpUrl = "http://39.100.105.180";
        $url = $httpUrl."/webservice/rest/server.php?wsfunction=enrol_self_enrol_user&wstoken={$token}&courseid={$course_id}&moodlewsrestformat=json";
        $res = postCurl($url);
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
            if($user->suspended){
                return $this->apiReturnJson('9999',null,'您的账号已被禁用');
            }
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

        $old = PasswordFindCode::where('user_id',$user->id)->where('type',1)->where('operation',3)->where('created_at','>',date('Y-m-d H:i:s',time()-60))->first();
        if($old){
            return $this->apiReturnJson("9998", null, '验证码发送频繁，请稍后再试');
        }
        $code = rand(100000, 999999);
        PasswordFindCode::create([
            'type'=>1,
            'user_id'=>$user->id,
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

    public function sendCodeByRegister()
    {
        $email = $this->request->get('email');
        $user = User::where('email', $email)->first();
        if($user){
            return $this->apiReturnJson("9999", null, '该邮箱已存在');
        }

        $old = PasswordFindCode::where('code','like',"$email%")->where('type',1)->where('operation',1)->where('created_at','>',date('Y-m-d H:i:s',time()-60))->first();
        if($old){
            return $this->apiReturnJson("9999", null, '验证码发送频繁，请稍后再试');
        }
        $code = rand(100000, 999999);
        PasswordFindCode::create([
            'type'=>1,
            'user_id'=>0,
            'operation'=>1,
            'status'=>0,
            'code'=>"$email-$code",
        ]);
        $this->requireMoodleConfig();

        try {
            \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\SendCodeEmail($code));
        } catch (\Exception $e) {

        }
        return $this->apiReturnJson("0", null, '发送成功');
    }

    public function register()
    {
        try {
            $this->requireMoodleConfig();
            global $CFG;
            require_once($CFG->dirroot . '/user/editlib.php');
            require_once($CFG->libdir . '/authlib.php');
            require_once($this->getMoodleRoot().'/login/lib.php');

            $user = $this->request->all();
            $user = json_decode(json_encode($user));
            //邮箱唯一性判断
            $userHas = $this->getUserByEmail($user->email);
            if($userHas){
                return $this->apiReturnJson("9998", null, '邮箱不能重复');
            }
            $password = $user->password;
            $checkPwd = $this->checkPassword($password);
            if(!$checkPwd){
                return $this->apiReturnJson('9999',null,'密码必须是6-16位字符，至少1个字母，1个数字和1个特殊字符(@$!%*#?&^*()_+=-)');
            }
            $codeHas = PasswordFindCode::where([
                ['type',1],
                ['operation',1],
                ['status',0],
                ['code',"{$user->email}-{$user->code}"],
            ])->first();
            if(!$codeHas){
                return $this->apiReturnJson('2002');
            }

            $user->username = $user->email;
            $user = signup_setup_new_user($user);
            $this->userSignup($user, true);

            if(isset($user->realname))
                $realname = $user->realname;
            else
                $realname = '';
            User::where('id', $user->id)->update([
                'confirmed'=>1,
                'firstname'=>$realname?substr_text($realname,0,1):'',
                'lastname'=>$realname?substr_text($realname,1, count($realname)):'',
            ]);
            UserBasicInfo::create(['user_id'=>$user->id]);
            $user = User::find($user->id);
            $token = TokenHelper::getTokenForUser($user);
            $user->token = $token->token;

            PasswordFindCode::where('id', $codeHas->id)->update(['status'=>1]);

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
        $checkPwd = $this->checkPassword($password);
        if(!$checkPwd){
            return $this->apiReturnJson('9999',null,'密码必须是6-16位字符，至少1个字母，1个数字和1个特殊字符(@$!%*#?&^*()_+=-)');
        }
        $codeHas = PasswordFindCode::where([
            ['user_id',$user->id],
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
            require_once($this->getMoodleRoot().'/user/lib.php');
            global $CFG;
            $CFG->passwordreuselimit = 10;
            user_add_password_history($user->id, $password);
            PasswordFindCode::where('id', $codeHas->id)->update(['status'=>1]);
            return $this->apiReturnJson('0',null,'密码修改成功');
        }
    }

    public function activate()
    {
        $password = $this->request->get('password');
        $user = TokenRepository::getUser();
        if(!$user) {
            return $this->apiReturnJson('9998');
        }
        $checkPwd = $this->checkPassword($password);
        if(!$checkPwd){
            return $this->apiReturnJson('9999',null,'密码必须是6-16位字符，至少1个字母，1个数字和1个特殊字符(@$!%*#?&^*()_+=-)');
        }
        //修改密码
        $this->requireMoodleConfig();

        $userauth = get_auth_plugin($user->auth);
        if (!$userauth->user_update_password($user, $password)) {
            return $this->apiReturnJson('9999');
        }else{
            require_once($this->getMoodleRoot().'/user/lib.php');
            global $CFG;
            $CFG->passwordreuselimit = 10;
            user_add_password_history($user->id, $password);
            return $this->apiReturnJson('0',null,'激活成功');
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
        require_once($this->getMoodleRoot().'/user/profile/lib.php');
        require_once($this->getMoodleRoot().'/user/lib.php');

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

//        if ($notify) {
            return true;
//        } else {
//            return true;
//        }
    }

    public function checkPassword($password)
    {
        $res = preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&^*()_+=-])[A-Za-z\d$@$!%*#?&^*()_+=-]{8,16}$/', $password);
//        $res = preg_match('/(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).{6,16}/', $password);
//        $res = preg_match('/^(\w*(?=\w*\d)(?=\w*[A-Za-z])\w*){6,16}$/', $password);
        return $res;
    }
}
