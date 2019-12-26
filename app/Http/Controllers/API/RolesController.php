<?php

namespace App\Http\Controllers\API;

use App\Models\CompanyRole;
use App\Repositories\UserRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RolesController extends ApiBaseCommonController
{
    protected $model_name = CompanyRole::class;

    public function authLimit(&$model)
    {
        $company_id = $this->getCurrentCompany()->id;
        $model = $model->where(function ($query)use($company_id){
            $query = $query->where('id', 1)->orWhere('company',$company_id);
        });
    }


    public function _after_get(&$data)
    {
        $userRepository = app()->build(UserRepository::class);
        foreach ($data as &$v) {
            $v->users = $userRepository->getUsersByRoleId($v->id);
        }
        return $data;
    }

    public function _after_find(&$data)
    {
        $userRepository = app()->build(UserRepository::class);
        $data->users = $userRepository->getUsersByRoleId($data->id);
        $data->permissions;
    }
}