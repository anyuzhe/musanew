<?php

namespace App\Repositories;

use App\Models\Entrust;
use App\Models\Job;
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

    public function getRecommend($recruit, $entrust)
    {
        $data = [];
        $recruitHas = [];
        $entrustHas = [];
        $leading = $recruit->leading;
        if($entrust){
            $step1 = Entrust::where('leading_id',$leading->user_id)->where('id','!=',$entrust->id)->where('status', 1)->where('is_public', 1)->get();
            if($step1->count()>0){
                foreach ($step1 as $s1) {
                    $data[] = [
                      'type'=>'entrust',
                      'id'=>$s1->id,
                    ];
                    $entrustHas[] = $s1->id;
                    if(count($data)>=3)
                        return $data;
                }
            }
        }else{
//            $step1 = Recruit::where('leading_id',$leading->user_id)->where('status', 1)->get();
            $step1 = Recruit::where('leading_id',$leading->user_id)->where('id','!=',$recruit->id)->where('status', 1)->where('is_public', 1)->get();
            if($step1->count()>0){
                foreach ($step1 as $s1) {
                    $data[] = [
                        'type'=>'recruit',
                        'id'=>$s1->id,
                    ];

                    $recruitHas[] = $s1->id;
                    if(count($data)>=3)
                        return $data;
                }
            }
        }
        if($entrust){
            $thirdParty = $entrust->thirdParty;
            $step2 = Entrust::where('is_public', 0)->where('third_party_id', $thirdParty->id)->where('id','!=',$entrust->id)->whereNotIn('id',$entrustHas)->where('status', 1)->where('is_public', 1)->get();

            foreach ($step2 as $s2) {
                $data[] = [
                    'type'=>'entrust',
                    'id'=>$s2->id,
                ];

                if(count($data)>=3)
                    return $data;
            }
        }else{
            $company = $recruit->company;
            $step2 = Recruit::where('is_public', 0)->where('company_id', $company->id)->where('id','!=',$recruit->id)->whereNotIn('id',$recruitHas)->where('status', 1)->where('is_public', 1)->get();

            foreach ($step2 as $s2) {
                $data[] = [
                    'type'=>'recruit',
                    'id'=>$s2->id,
                ];

                if(count($data)>=3)
                    return $data;
            }
        }

        $jobIds = Job::where('name', 'like', "%{$recruit->job->name}%")->pluck('id')->toArray();

        $step3 = Recruit::where('is_public', 1)->whereIn('id', $jobIds)->where('id','!=',$recruit->id)->whereNotIn('id',$recruitHas)->where('status', 1)->get();
        foreach ($step3 as $s3) {
            $data[] = [
                'type'=>'recruit',
                'id'=>$s3->id,
            ];

            if(count($data)>=3)
                return $data;
        }

        $step4 = Recruit::where('is_public', 1)->where('id','!=',$recruit->id)->where('status', 1)->whereNotIn('id',$recruitHas)->get();
        foreach ($step4 as $s4) {
            $data[] = [
                'type'=>'recruit',
                'id'=>$s4->id,
            ];

            if(count($data)>=3)
                return $data;
        }

        return $data;
    }
}
