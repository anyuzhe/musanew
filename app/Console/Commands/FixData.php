<?php

namespace App\Console\Commands;

use App\Models\CompanyPermission;
use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Models\ResumeSkill;
use App\Models\Skill;
use App\Models\UserBasicInfo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data {type} {--check}';

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
        }elseif ($type==2){
            $recruits = Recruit::whereIn('status', [6,7])->get();
            foreach ($recruits as $recruit) {
                foreach ($recruit->entrusts as $entrust) {
                    if($entrust->status!=6) {
                        $entrust->status = 6;
                        $entrust->pause_at = $recruit->pause_at;
                        $entrust->save();
                    }
                }
            }
            $this->info('修复暂停数据成功');
        }elseif ($type==3){
            CompanyPermission::truncate();
            $rm = CompanyPermission::create([
                'key'=>'recruit_manage',
                'display_name'=>'招聘管理',
                'level'=>'1',
                'pid'=>'',
            ]);
            $jm = CompanyPermission::create([
                'key'=>'job_manage',
                'display_name'=>'职位管理',
                'level'=>'2',
                'pid'=>$rm->id,
            ]);
            CompanyPermission::create([
                'key'=>'add_official_job',
                'display_name'=>'添加/复制企业正式职位',
                'level'=>'3',
                'pid'=>$jm->id,
            ]);
            CompanyPermission::create([
                'key'=>'edit_official_job',
                'display_name'=>'编辑企业正式职位',
                'level'=>'3',
                'pid'=>$jm->id,
            ]);
            CompanyPermission::create([
                'key'=>'delete_official_job',
                'display_name'=>'删除企业正式职位',
                'level'=>'3',
                'pid'=>$jm->id,
            ]);
            CompanyPermission::create([
                'key'=>'show_official_job',
                'display_name'=>'查看/筛查企业正式职位',
                'level'=>'3',
                'pid'=>$jm->id,
            ]);

            CompanyPermission::create([
                'key'=>'add_gain_job',
                'display_name'=>'复制获取职位',
                'level'=>'3',
                'pid'=>$jm->id,
            ]);
            CompanyPermission::create([
                'key'=>'edit_gain_job',
                'display_name'=>'编辑获取职位',
                'level'=>'3',
                'pid'=>$jm->id,
            ]);
            CompanyPermission::create([
                'key'=>'delete_gain_job',
                'display_name'=>'删除获取职位',
                'level'=>'3',
                'pid'=>$jm->id,
            ]);
            CompanyPermission::create([
                'key'=>'show_gain_job',
                'display_name'=>'查看/筛查获取职位',
                'level'=>'3',
                'pid'=>$jm->id,
            ]);


            $um = CompanyPermission::create([
                'key'=>'recruit_user_manage',
                'display_name'=>'人员管理',
                'level'=>'2',
                'pid'=>$rm->id,
            ]);

            CompanyPermission::create([
                'key'=>'add_recruit',
                'display_name'=>'添加招聘职位',
                'level'=>'3',
                'pid'=>$um->id,
            ]);
            CompanyPermission::create([
                'key'=>'add_entrust',
                'display_name'=>'委托外包',
                'level'=>'3',
                'pid'=>$um->id,
            ]);
            CompanyPermission::create([
                'key'=>'pause_recruit',
                'display_name'=>'暂停招聘',
                'level'=>'3',
                'pid'=>$um->id,
            ]);
            CompanyPermission::create([
                'key'=>'end_recruit',
                'display_name'=>'结束招聘',
                'level'=>'3',
                'pid'=>$um->id,
            ]);
            CompanyPermission::create([
                'key'=>'send_resume',
                'display_name'=>'添加/推荐/导入简历',
                'level'=>'3',
                'pid'=>$um->id,
            ]);
            CompanyPermission::create([
                'key'=>'edit_recruit',
                'display_name'=>'编辑招聘',
                'level'=>'3',
                'pid'=>$um->id,
            ]);
            CompanyPermission::create([
                'key'=>'get_job',
                'display_name'=>'获取职位',
                'level'=>'3',
                'pid'=>$um->id,
            ]);
            CompanyPermission::create([
                'key'=>'show_regular_employee',
                'display_name'=>'查看/筛查企业正式员工',
                'level'=>'3',
                'pid'=>$um->id,
            ]);
            CompanyPermission::create([
                'key'=>'show_outsourcing_employee',
                'display_name'=>'查看/筛查外包员工职位',
                'level'=>'3',
                'pid'=>$um->id,
            ]);
            CompanyPermission::create([
                'key'=>'show_demand',
                'display_name'=>'查看/筛查需求管理',
                'level'=>'3',
                'pid'=>$um->id,
            ]);
            CompanyPermission::create([
                'key'=>'handle_resume',
                'display_name'=>'操作简历',
                'level'=>'3',
                'pid'=>$um->id,
            ]);


            $em = CompanyPermission::create([
                'key'=>'entrust_manage',
                'display_name'=>'委托申请',
                'level'=>'2',
                'pid'=>$rm->id,
            ]);

            CompanyPermission::create([
                'key'=>'show_entrust',
                'display_name'=>'查看',
                'level'=>'3',
                'pid'=>$em->id,
            ]);

            CompanyPermission::create([
                'key'=>'agree_entrust',
                'display_name'=>'同意/拒绝',
                'level'=>'3',
                'pid'=>$em->id,
            ]);

            $om = CompanyPermission::create([
                'key'=>'outsourcing_manage',
                'display_name'=>'合作外包',
                'level'=>'2',
                'pid'=>$rm->id,
            ]);

            CompanyPermission::create([
                'key'=>'show_outsourcing',
                'display_name'=>'查看',
                'level'=>'3',
                'pid'=>$om->id,
            ]);

            CompanyPermission::create([
                'key'=>'end_outsourcing',
                'display_name'=>'结束招聘',
                'level'=>'3',
                'pid'=>$om->id,
            ]);

            $rm = CompanyPermission::create([
                'key'=>'resume_manage',
                'display_name'=>'简历管理',
                'level'=>'2',
                'pid'=>$rm->id,
            ]);

            CompanyPermission::create([
                'key'=>'show_resume',
                'display_name'=>'查看/筛选简历中心',
                'level'=>'3',
                'pid'=>$rm->id,
            ]);

            CompanyPermission::create([
                'key'=>'show_entry_resume',
                'display_name'=>'查看/筛选入职成功简历',
                'level'=>'3',
                'pid'=>$rm->id,
            ]);
            CompanyPermission::create([
                'key'=>'show_blacklist_resume',
                'display_name'=>'查看/筛选黑名单',
                'level'=>'3',
                'pid'=>$rm->id,
            ]);
            CompanyPermission::create([
                'key'=>'add_resume',
                'display_name'=>'添加/导入简历',
                'level'=>'3',
                'pid'=>$rm->id,
            ]);
            CompanyPermission::create([
                'key'=>'export_resume',
                'display_name'=>'导出简历',
                'level'=>'3',
                'pid'=>$rm->id,
            ]);
            CompanyPermission::create([
                'key'=>'update_resume',
                'display_name'=>'编辑简历',
                'level'=>'3',
                'pid'=>$rm->id,
            ]);
            CompanyPermission::create([
                'key'=>'delete_resume',
                'display_name'=>'删除简历',
                'level'=>'3',
                'pid'=>$rm->id,
            ]);
            CompanyPermission::create([
                'key'=>'upload_accessory',
                'display_name'=>'上传附件',
                'level'=>'3',
                'pid'=>$rm->id,
            ]);
            CompanyPermission::create([
                'key'=>'move_to_blacklist',
                'display_name'=>'移至黑名单',
                'level'=>'3',
                'pid'=>$rm->id,
            ]);
            CompanyPermission::create([
                'key'=>'recommend_job',
                'display_name'=>'推荐职位',
                'level'=>'3',
                'pid'=>$rm->id,
            ]);
            ////--///
            ///
            $cm = CompanyPermission::create([
                'key'=>'company_manage',
                'display_name'=>'企业信息',
                'level'=>'1',
                'pid'=>'',
            ]);
            $bm = CompanyPermission::create([
                'key'=>'basics_manage',
                'display_name'=>'基础信息',
                'level'=>'2',
                'pid'=>$cm->id,
            ]);
            CompanyPermission::create([
                'key'=>'show_basics',
                'display_name'=>'查看',
                'level'=>'3',
                'pid'=>$bm->id,
            ]);
            CompanyPermission::create([
                'key'=>'edit_basics',
                'display_name'=>'编辑',
                'level'=>'3',
                'pid'=>$bm->id,
            ]);
            CompanyPermission::create([
                'key'=>'edit_manager',
                'display_name'=>'更换管理员',
                'level'=>'3',
                'pid'=>$bm->id,
            ]);
            $bm = CompanyPermission::create([
                'key'=>'department_manage',
                'display_name'=>'部门管理',
                'level'=>'2',
                'pid'=>$cm->id,
            ]);
            CompanyPermission::create([
                'key'=>'show_department',
                'display_name'=>'查看',
                'level'=>'3',
                'pid'=>$bm->id,
            ]);
            CompanyPermission::create([
                'key'=>'add_department',
                'display_name'=>'新增',
                'level'=>'3',
                'pid'=>$bm->id,
            ]);
            CompanyPermission::create([
                'key'=>'edit_department',
                'display_name'=>'编辑',
                'level'=>'3',
                'pid'=>$bm->id,
            ]);
            CompanyPermission::create([
                'key'=>'delete_department',
                'display_name'=>'删除',
                'level'=>'3',
                'pid'=>$bm->id,
            ]);
            $bm = CompanyPermission::create([
                'key'=>'role_manage',
                'display_name'=>'角色管理',
                'level'=>'2',
                'pid'=>$cm->id,
            ]);
            CompanyPermission::create([
                'key'=>'show_role',
                'display_name'=>'查看',
                'level'=>'3',
                'pid'=>$bm->id,
            ]);
            CompanyPermission::create([
                'key'=>'add_role',
                'display_name'=>'新增/复制',
                'level'=>'3',
                'pid'=>$bm->id,
            ]);
            CompanyPermission::create([
                'key'=>'edit_role',
                'display_name'=>'编辑',
                'level'=>'3',
                'pid'=>$bm->id,
            ]);
            CompanyPermission::create([
                'key'=>'delete_role',
                'display_name'=>'删除',
                'level'=>'3',
                'pid'=>$bm->id,
            ]);
            $bm = CompanyPermission::create([
                'key'=>'company_user_manage',
                'display_name'=>'人员管理',
                'level'=>'2',
                'pid'=>$cm->id,
            ]);
            CompanyPermission::create([
                'key'=>'show_user',
                'display_name'=>'查看',
                'level'=>'3',
                'pid'=>$bm->id,
            ]);
            CompanyPermission::create([
                'key'=>'add_user',
                'display_name'=>'新增',
                'level'=>'3',
                'pid'=>$bm->id,
            ]);
            CompanyPermission::create([
                'key'=>'edit_user',
                'display_name'=>'编辑',
                'level'=>'3',
                'pid'=>$bm->id,
            ]);
            CompanyPermission::create([
                'key'=>'delete_user',
                'display_name'=>'删除',
                'level'=>'3',
                'pid'=>$bm->id,
            ]);
            $bm = CompanyPermission::create([
                'key'=>'resume_score_setting',
                'display_name'=>'简历评分配置',
                'level'=>'2',
                'pid'=>$cm->id,
            ]);
            CompanyPermission::create([
                'key'=>'show_setting',
                'display_name'=>'查看',
                'level'=>'3',
                'pid'=>$bm->id,
            ]);
            CompanyPermission::create([
                'key'=>'add_setting',
                'display_name'=>'新增',
                'level'=>'3',
                'pid'=>$bm->id,
            ]);
            CompanyPermission::create([
                'key'=>'edit_setting',
                'display_name'=>'编辑',
                'level'=>'3',
                'pid'=>$bm->id,
            ]);
            $bm = CompanyPermission::create([
                'key'=>'operation_log',
                'display_name'=>'操作日志',
                'level'=>'2',
                'pid'=>$cm->id,
            ]);
            CompanyPermission::create([
                'key'=>'show_log',
                'display_name'=>'查看',
                'level'=>'3',
                'pid'=>$bm->id,
            ]);
            CompanyPermission::create([
                'key'=>'excel_export',
                'display_name'=>'excel导出',
                'level'=>'3',
                'pid'=>$bm->id,
            ]);
            ////--///
            ///
            $da = CompanyPermission::create([
                'key'=>'data_analysis',
                'display_name'=>'数据分析',
                'level'=>'1',
                'pid'=>'',
            ]);
            $ta = CompanyPermission::create([
                'key'=>'third_party_analysis',
                'display_name'=>'外包/需求方数据分析',
                'level'=>'2',
                'pid'=>$da->id,
            ]);
            CompanyPermission::create([
                'key'=>'show_third_party_analysis',
                'display_name'=>'查看',
                'level'=>'3',
                'pid'=>$ta->id,
            ]);
            $sd = CompanyPermission::create([
                'key'=>'select_data',
                'display_name'=>'选择数据',
                'level'=>'2',
                'pid'=>$da->id,
            ]);
            CompanyPermission::create([
                'key'=>'show_select_data',
                'display_name'=>'查看/操作',
                'level'=>'3',
                'pid'=>$sd->id,
            ]);
            $this->info('添加公司权限数据成功');


            $t = CompanyPermission::where('level', 1)->get();

            foreach ($t as $v) {
                $v->full_key = $v->key;
                $v->save();
            }

            $t = CompanyPermission::where('level', 2)->get();

            foreach ($t as $v) {
                $v->full_key = $v->parent->full_key.'.'.$v->key;
                $v->save();
            }

            $t = CompanyPermission::where('level', 3)->get();

            foreach ($t as $v) {
                $v->full_key = $v->parent->full_key.'.'.$v->key;
                $v->save();
            }

        }
        elseif ($type==4){

            $t = CompanyPermission::where('level', 1)->get();
            foreach ($t as $v) {
                $v->full_key = $v->key;
                $v->save();
            }

            $t = CompanyPermission::where('level', 2)->get();
            foreach ($t as $v) {
                $v->full_key = $v->parent->full_key.'.'.$v->key;
                $v->save();
            }

            $t = CompanyPermission::where('level', 3)->get();
            foreach ($t as $v) {
                $v->full_key = $v->parent->full_key.'.'.$v->key;
                $v->save();
            }
        }
        elseif ($type==5){
            $infos = UserBasicInfo::all();
            foreach ($infos as $info) {
                if(!$info->email && $info->user && $info->user->email){
                    $info->email = $info->user->email;
                    $info->save();
                }
            }
        }
        elseif ($type==6){

            $isCheck = $this->option('check');
            if($isCheck)
                $this->info('检查模式');
            $recruits = Recruit::all();
            foreach ($recruits as $recruit) {
                $resume_num = RecruitResume::where('company_job_recruit_id', $recruit->id)->count();
                if($resume_num!=$recruit->resume_num){
                    if($isCheck){
                        dump($resume_num);
                        dump($recruit->resume_num);
                        dd($recruit->id);
                    }
                    DB::connection('musa')->table('company_job_recruit')->where('id', $recruit->id)->update(['resume_num'=>$resume_num]);
                    $entrusts = $recruit->entrusts;
                    foreach ($entrusts as $entrust) {
                        $resume_num = RecruitResume::where('company_job_recruit_id', $recruit->id)->where('company_job_recruit_entrust_id', $entrust->id)->count();
                        if ($resume_num != $entrust->resume_num) {
                            DB::connection('musa')->table('company_job_recruit_entrust')->where('id', $entrust->id)->update(['resume_num'=>$resume_num]);
                        }
                    }
                }
            }
        }
    }
}
