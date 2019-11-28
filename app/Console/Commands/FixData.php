<?php

namespace App\Console\Commands;

use App\Models\Skill;
use Illuminate\Console\Command;

class FixData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data {type}';

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
        $type = $this->argument('type');
        if($type==1){
            $skills = Skill::all();
            foreach ($skills as $skill) {
                $skill->name = trim($skill->name);
                $skill->save();
            }
            $this->info('修复技能数据成功');
        }
    }
}
