<?php
/**
 * Created by PhpStorm.
 * User: zhenglong
 * Date: 2019/6/20
 * Time: 4:12 PM
 */

namespace App\ZL\Moodle;


use App\Models\User;
use App\Models\UserBasicInfo;

class UserHelper
{
    public static function register($user)
    {
        requireMoodleConfig();

        global $CFG;
        require_once($CFG->dirroot . '/user/editlib.php');
        require_once($CFG->libdir . '/authlib.php');
        require_once(getMoodleRoot().'/login/lib.php');

        $user = signup_setup_new_user($user);
        self::userSignup($user, true);

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
    }


    protected static function userSignup(&$user, $notify=true, $confirmationurl = null)
    {
        global $CFG, $DB, $SESSION;
        require_once(getMoodleRoot() . '/user/profile/lib.php');
        require_once(getMoodleRoot() . '/user/lib.php');

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