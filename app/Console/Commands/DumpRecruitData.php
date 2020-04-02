<?php

namespace App\Console\Commands;

use App\Models\Recruit;
use Illuminate\Console\Command;

class DumpRecruitData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dp:recruit';

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
        $recruits = Recruit::all();
        foreach ($recruits as $recruit) {
        }
    }
}
