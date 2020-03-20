<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\Company;
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
use App\Repositories\UserRepository;
use App\ZL\Controllers\ApiBaseCommonController;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UsersController extends ApiBaseCommonController
{
    protected $model_name = User::class;
    protected $userRepository;

    public $search_field_array = [
//        ['xxx','like'],
//        ['xxx','='],
    ];

    public function __construct(Request $request, UserRepository $userRepository)
    {
        parent::__construct($request);
        $this->userRepository = $userRepository;
    }

    public function authLimit(&$model)
    {
        $text = $this->request->get('text');
        if($text){
            $companyIds = Company::where('company_name', 'like', "%$text%")->orWhere('company_alias', 'like', "%$text%")->pluck('id')->unique()->toArray();
            $userIds = DB::connection('musa')->table('company_user')->whereIn('company_id', $companyIds)->pluck('user_id')->unique()->toArray();
            $userIds1 = UserBasicInfo::where('realname', 'like', "%$text%")->pluck('user_id');
            $userIds1 = $userIds1->merge($userIds);
            $userIds = $userIds1->toArray();
            $model = $model->where(function ($query) use ($text, $userIds) {
                $query->whereIn('id', $userIds)->orWhere('email', 'like', "%$text%")->orWhere('id', 'like', "%$text%");
            });
        }
        $model = $model->where('deleted', 0)->whereIn('id', UserBasicInfo::pluck('user_id')->toArray());
    }

    public function tree()
    {
        $skills = SkillsRepository::getTree();
        return $this->apiReturnJson(0, $skills);
    }

    public function afterStore($obj, $data)
    {
//        $info = new UserBasicInfo();
//        $info->user_id = $obj->id;
//        $info->fill($data);
//        $info->save();
//        if(!$obj->firstname && $info->realname){
//            $realname = $info->realname;
//            User::where('id', $obj->id)->update([
//                'firstname'=>$realname?substr_text($realname,0,1):'',
//                'lastname'=>$realname?substr_text($realname,1, strlen($realname)):'',
//            ]);
//        }
        return $this->apiReturnJson(0);
    }


    public function afterUpdate($id, $data)
    {
//        $info = $data->info;
//        $info->fill($data);
//        $info->save();
//        $user = User::find($id);
//        if(!$user->firstname && $info->realname){
//            $realname = $info->realname;
//            User::where('id', $user->id)->update([
//                'firstname'=>$realname?substr_text($realname,0,1):'',
//                'lastname'=>$realname?substr_text($realname,1, strlen($realname)):'',
//            ]);
//        }
        return $this->apiReturnJson(0);
    }

    public function _after_get(&$data)
    {
        $data->load('info');
        $infos = [];
        foreach ($data as &$v) {
            $infos[] = $this->userRepository->getInfo($v);
        }
        $newData = $data->toArray();
        foreach ($newData as $k=>&$newDatum) {
            $_info = $infos[$k];
            $_info['created_at'] = date('Y-m-d H:i:s', $newDatum['timecreated']);
            $newDatum['info'] = $_info;
        }
        return $newData;
    }

    public function _after_find(&$data)
    {
        $info = $this->userRepository->getInfo($data);
        $data = $data->toArray();
        $data['info'] = $info;
    }

    public function destroy($id)
    {
        $model = $this->getModel()->find($id);
        $model->status = -1;
        $model->save();
        return responseZK(0);
    }
}
