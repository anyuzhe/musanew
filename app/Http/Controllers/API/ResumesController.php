<?php

namespace App\Http\Controllers\API;

use App\Models\ExtendMap;
use App\Models\Resume;
use App\ZL\Controllers\ApiBaseCommonController;
use Illuminate\Support\Facades\DB;

class ResumesController extends ApiBaseCommonController
{
    public $model_name = Resume::class;

    public function view()
    {
        $user = $this->getUser();
        $resume = $user->resume;
        $resume = DB::table('resume_basic')->where('resumeid', '=', $resume->id)->first();
        //获取公司信息
        $resume->company = DB::table('resume_company')
            ->where('resumeid', '=', $resume->resumeid)
            ->orderby('id', 'desc')->get();

        //获取公司信息
        $resume->education = DB::table('resume_education')
            ->where('resumeid', '=', $resume->resumeid)
            ->orderby('id', 'desc')->get();


        //获取语言信息
        $resume->language = DB::table('resume_language')
            ->where('resumeid', '=', $resume->resumeid)
            ->orderby('id', 'desc')->get();

        //获取语言信息
        $resume->project = DB::table('resume_project')
            ->where('resumeid', '=', $resume->resumeid)
            ->orderby('id', 'desc')->get();

        //获取技能信息
        $resume->skill = DB::table('resume_skill')
            ->leftjoin('skills', 'resume_skill.skillid', '=', 'skills.id')
            ->where('resume_skill.resumeid', '=', $resume->resumeid)
            ->orderby('resume_skill.id', 'desc')->get();

        return $this->apiReturnJson(0, $resume);
    }


//    public function edit()
//    {
//        $request = $this->request->all();
//        if (isset($request['token'])) {
//            unset($request['token']);
//        }
//        $user = $this->getUser();
//        $userid = $user->id;
//        $resume = $user->resume;
//
//        switch ($request['category']) {
//            case 'basic':
//                $data = array(
//                    'user_photo' => $request['user_photo'] ?? '',        //照片
//                    'education' => $request['education'] ?? '',            //教育程度
//                    'work_nature' => $request['work_nature'] ?? '',
//                    'startwork' => $request['startwork'] ?? '',
//                    'jobstatus' => $request['jobstatus'] ?? '',
//                    'industry' => $request['industry'] ?? '',
//                    'career' => $request['career'] ?? '',
//                    'salary_min' => $request['salary_min'] ?? '',
//                    'salary_max' => $request['salary_max'] ?? '',
//                    'permanent_provinceid' => $request['permanent_provinceid'] ?? null,
//                    'permanent_cityid' => $request['permanent_cityid'] ?? '',
//                    'residence_provinceid' => $request['residence_provinceid'] ?? null,
//                    'residence_cityid' => $request['residence_cityid'] ?? null,
//                    'residence_distinctid' => $request['residence_distinctid'] ?? null,
//                    'residence_address' => $request['residence_address'] ?? '',
//                );
//                $basicid = $resume->basic->insert($data);
//                break;
//            case 'education':
//                $filters = ['schoolname', 'major', 'start', 'end', 'national', 'degree'];
//                $data = $this->requestParamFilter($filters, $request);
//                if ($request['educationid']) {
//                    $rt = DB::table('resume_education')->where('id', $request['educationid'])->update($data);
//                    if ($rt) {
//
//                    }
//                } else {
//                    $rt = DB::table('resume_education')->insert($data);
//                }
//
//            default:
//                break;
//        }
//        if (!$resume) {
//            DB::table('resume')->insert($s);
//        } else {
//            $resumeid = $resume->id;
//        }
//    }
}
