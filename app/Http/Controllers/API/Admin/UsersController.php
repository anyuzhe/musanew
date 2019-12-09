<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\CommonController;
use App\Models\CompanyRole;
use App\Models\CompanyUser;
use App\Models\Course;
use App\Models\Resume;
use App\Models\ResumeEducation;
use App\Models\User;
use App\Models\UserBasicInfo;
use App\Repositories\RecruitResumesRepository;
use App\Repositories\ResumesRepository;
use App\Repositories\SkillsRepository;
use App\Repositories\TestsRepository;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UsersController extends CommonController
{
    protected $resumeRepository;
    protected $recruitResumesRepository;

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function info()
    {
    }
}
