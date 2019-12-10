<?php

namespace App\Http\Controllers\API\Admin;

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

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function authList()
    {
        $admin = $this->getAdmin();
        return $this->apiReturnJson(0, [
            'info'=>$admin,
            'menu_list'=>$admin->getFrontMenuList(),
            'permissions'=>$admin->permissions()->pluck('front_key')->unique()->toArray(),
        ]);
    }

    public function info()
    {
        $admin = $this->getAdmin();
        return $this->apiReturnJson(0,$admin);
    }
}
