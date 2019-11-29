<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DumpUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dump:user';

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
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            $this->info("id:{$user->id}, username:{$user->username}, email:{$user->email}");
        }
    }
}
