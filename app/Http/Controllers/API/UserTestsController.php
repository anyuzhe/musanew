<?php

namespace App\Http\Controllers\API;

use App\Models\CompanyResume;
use App\Models\Entrust;
use App\Models\Recruit;
use App\Models\RecruitResume;
use App\Models\RecruitResumeLog;
use App\Models\Resume;
use App\Models\ResumeAttachment;
use App\Models\ResumeSkill;
use App\Repositories\RecruitResumesRepository;
use App\Repositories\ResumesRepository;
use App\Repositories\TestsRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Constraint;
use Intervention\Image\Facades\Image;
use TCG\Voyager\Facades\Voyager;

class UserTestsController extends ApiBaseCommonController
{
    public $model_name = Resume::class;
    protected $testsRepository;
    protected $recruitResumeRepository;

    public function __construct(Request $request, TestsRepository $testsRepository, RecruitResumesRepository $recruitResumeRepository)
    {
        parent::__construct($request);
        $this->testsRepository = $testsRepository;
        $this->recruitResumeRepository = $recruitResumeRepository;
    }

    public function getTestByRecruitId($id)
    {
        $data = [];
        $recruit = Recruit::find($id);
        $job = $recruit->job;
        $tests = $job->tests;
        $user = $this->getUser();
        foreach ($tests as $test) {
            $data[] = $this->testsRepository->getTestData($test, $user);
        }
        return $this->apiReturnJson(0, $data);
    }

    public function getMatching(Request $request)
    {
        $resume_id = $request->get('resume_id');
        $recruit_id = $request->get('recruit_id');

        $resume = Resume::find($resume_id);
        $recruit = Recruit::find($recruit_id);
        if(!$resume | !$recruit){
            return $this->apiReturnJson(9999,null,'缺少参数');
        }
        $std = new \stdClass();
        $std->job = $recruit->job;
        $std->resume = $resume;
        $data = $this->recruitResumeRepository->matching($std);
        return $this->apiReturnJson(0, $data);
    }
}
