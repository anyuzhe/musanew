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
    protected $search_field_array = [
      ['name','like']
    ];
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
            'permissions'=>$admin->frontPermissions(),
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
            $v->role_names = implode(', ', $v->roles_all->pluck('display_name')->toArray());
            unset($v->roles_all);
        }
        return $data;
    }

    public function _after_find(&$data)
    {
        $data->role_ids = $data->roles_all()->pluck('id')->toArray();
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

    public function afterStore($obj, $data)
    {
        $role_ids = $this->request->get('role_ids');
        $obj->roles()->sync($role_ids);
        return $this->apiReturnJson(0);
    }

    public function afterUpdate($id, $data, $obj)
    {
        $role_ids = $this->request->get('role_ids');
        $obj->roles()->sync($role_ids);
        return $this->apiReturnJson(0);
    }
}
