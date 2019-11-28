<?php

namespace App\Console\Commands;

use App\Models\ResumeSkill;
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
            //修复技能数据
            $oldSkills = Skill::all();
            $oldSkillsData = [];
            foreach ($oldSkills as $oldSkill) {
                $name = strtolower($oldSkill->name);
                if(!isset($oldSkillsData[$name])){
                    $oldSkillsData[$name] = $oldSkill;
                }
            }

            $skills = Skill::all();
            foreach ($skills as $skill) {
                $_name = strtolower($skill->name);
                $skill->name = trim($skill->name);
                $skill->save();

                if($skill->category_l2_id==23 && isset($oldSkillsData[$_name]) &&$oldSkillsData[$_name]->id!=$skill->id){
                    ResumeSkill::where('skill_id',$skill->id)->update(['skill_id'=>$oldSkillsData[$_name]->id]);
                    $skill->delete();
                }
            }
            $this->info('修复技能数据成功');
        }
    }
}
