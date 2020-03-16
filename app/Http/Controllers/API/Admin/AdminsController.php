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
use App\ZL\Controllers\ApiBaseCommonController;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminsController extends ApiBaseCommonController
{
    protected $model_name = \App\User::class;

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
            'permissions'=>$admin->permissions()->pluck('front_key')->unique()->values()->toArray(),
        ]);
    }

    public function _after_get(&$data)
    {
        foreach ($data as &$v) {
            $v->roles_all = $v->roles_all();
            unset($v->role);
            unset($v->roles);
            unset($v->settings);
        }
        foreach ($data as &$v) {
            $v->roles =$v->roles_all;
            unset($v->roles_all);
        }
        return $data;
    }

    public function _after_find(&$data)
    {
        $_data = $data->toArray();
        $_data['roles'] = $data->roles_all();
        unset($_data['settings']);
        $data = $_data;
    }

    public function info()
    {
        $admin = $this->getAdmin();
        return $this->apiReturnJson(0,$admin);
    }
}
