<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\Admin\Role;
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

class RolesController extends ApiBaseCommonController
{
    protected $model_name = Role::class;
    protected $search_field_array = [
      ['display_name','like']
    ];
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }


    public function _after_get(&$data)
    {
        return $data;
    }

    public function _after_find(&$data)
    {
        $data->menu_ids = $data->frontMenus->flatten()->pluck('id')->unique()->toArray();
    }


    public function afterStore($obj, $data)
    {
        $menu_ids = $this->request->get('menu_ids');
        $obj->frontMenus()->sync($menu_ids);
        return $this->apiReturnJson(0);
    }

    public function afterUpdate($id, $data, $obj)
    {
        $menu_ids = $this->request->get('menu_ids');
        $obj->frontMenus()->sync($menu_ids);
        return $this->apiReturnJson(0);
    }
}
