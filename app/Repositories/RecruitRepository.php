<?php

namespace App\Repositories;

use App\Models\Recruit;
use App\Models\RecruitEndLog;

class RecruitRepository
{
    public function generateEndLog(Recruit $recruit)
    {
        RecruitEndLog::create([
            'company_id'=>$recruit->company_id,
            'job_id'=>$recruit->job_id,
            'company_job_recruit_id'=>$recruit->id,
            'need_num'=>$recruit->need_num,
            'done_num'=>$recruit->done_num,
            'resume_num'=>$recruit->resume_num,
            'new_resume_num'=>$recruit->new_resume_num,
            'start_at'=>$recruit->created_at,
            'end_at'=>$recruit->end_at,
        ]);
    }
}
