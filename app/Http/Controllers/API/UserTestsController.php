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

    public function __construct(Request $request, TestsRepository $testsRepository)
    {
        parent::__construct($request);
        $this->testsRepository = $testsRepository;
    }

    public function getTestByRecruitId($id)
    {
        $data = [];
        $recruit = Recruit::find($id);
        $job = $recruit->job;
        $tests = $job->tests;
        $user = $this->getUser();
        foreach ($tests as $test) {
            $this->testsRepository->getTestData($test, $user);
        }
    }
}
