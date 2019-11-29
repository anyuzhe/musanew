<?php

namespace App\Console\Commands;

use App\Models\PasswordFindCode;
use App\Models\User;
use Illuminate\Console\Command;

class EditPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'edit:password {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //%TGBnhy6
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::where('email', $email)->first();
        if(!$user)
            $this->error('找不到用户');
        define('CLI_SCRIPT', true);
        requireMoodleConfig();

        $userauth = get_auth_plugin($user->auth);
        dd(2);
        if (!$userauth->user_update_password($user, $password)) {
            $this->error('error');
        }else{
            require_once(getMoodleRoot().'/user/lib.php');
            global $CFG;
            $CFG->passwordreuselimit = 10;
            user_add_password_history($user->id, $password);
            $this->info('密码修改成功');
        }
    }
}
