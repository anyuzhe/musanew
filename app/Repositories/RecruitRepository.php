<?php

namespace App\Repositories;

use App\Models\Entrust;
use App\Models\Recruit;
use App\Models\RecruitEndLog;

class RecruitRepository
{
    public function generateEndLog(Recruit $recruit, Entrust $entrust=null)
    {
        RecruitEndLog::create([
            'company_id'=>$recruit->company_id,
            'third_party_id'=>$entrust?$entrust->third_party_id:null,
            'job_id'=>$recruit->job_id,
            'company_job_recruit_id'=>$recruit->id,
            'company_job_recruit_entrust_id'=>$entrust?$entrust->id:null,
            'need_num'=>$recruit->need_num,
            'done_num'=>$entrust?$entrust->done_num:$recruit->done_num,
            'resume_num'=>$entrust?$entrust->resume_num:$recruit->resume_num,
            'new_resume_num'=>$entrust?$entrust->new_resume_num:$recruit->new_resume_num,
            'start_at'=>$entrust?$entrust->created_at:$recruit->created_at,
            'status'=>$entrust?$entrust->status:$recruit->status,
            'end_at'=>$entrust?$entrust->end_at:$recruit->end_at,
        ]);
    }
}
