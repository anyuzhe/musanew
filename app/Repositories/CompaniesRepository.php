<?php

namespace App\Repositories;

use App\Models\CompanyAddress;
use App\Models\CompanyDepartment;
use App\Models\CompanyRole;
use App\Models\CompanyUserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CompaniesRepository
{
    public function getDepartmentTree($company_id)
    {
        $all = CompanyDepartment::where('company_id',$company_id)->get()->toArray();
        $data = [];
        foreach ($all as $v) {
            if($v['pid']==0){
                $this->getChild($v, $all);
                $data[] = $v;
            }
        }
        return $data;
    }

    protected function getChild(&$v, $all)
    {
        $v['children'] = [];
        foreach ($all as $item) {
            if($item['pid']==$v['id']){
                $this->getChild($item, $all);
                $v['children'][] = $item;
            }
        }
        if(count($v['children'])==0){
            $v['children'] = null;
        }
    }

    public function saveAddressesAndDepartments($addresses, $departments, $company_id)
    {
        if($addresses && is_array($addresses)){
            $addresses_ids = [];
            foreach ($addresses as $address) {
                $address['company_id'] = $company_id;
                if(isset($address['id']) && $address['id']){
                    $addresses_ids[] = $address['id'];
                    $_address = CompanyAddress::find($address['id']);
                    $_address->fill($address);

                    if(isset($address['area']) && is_array($address['area'])){
                        if(isset($address['area'][0]))
                            $_address->province_id = $address['area'][0];
                        if(isset($address['area'][1]))
                            $_address->city_id = $address['area'][1];
                        if(isset($address['area'][2]))
                            $_address->district_id = $address['area'][2];
                    }

                    $_address->save();
                }else{
                    if(isset($address['area']) && is_array($address['area'])){
                        if(isset($address['area'][0]))
                            $address['province_id'] = $address['area'][0];
                        if(isset($address['area'][1]))
                            $address['city_id'] = $address['area'][1];
                        if(isset($address['area'][2]))
                            $address['district_id'] = $address['area'][2];
                    }
                    $obj = CompanyAddress::create($address);
                    $addresses_ids[] = $obj->id;
                }
            }
//            CompanyAddress::where('company_id', $company_id)->whereNotIn('id', $addresses_ids)->delete();//真删除
            CompanyAddress::where('company_id', $company_id)->whereNotIn('id', $addresses_ids)->update(['company_id'=>null]);//假删除
        }
        if($departments && is_array($departments)){
            $departments_ids = [];
            foreach ($departments as $department) {
                if(!$department['name'])
                    continue;
                $department['company_id'] = $company_id;
                if(isset($department['id']) && $department['id']){
                    $_department = CompanyDepartment::find($department['id']);
                    $_department->fill($department);
                    $_department->save();
                    if(isset($department['children']) && count($department['children'])>0){
                        foreach ($department['children'] as $item) {
                            if(!$item['name'])
                                continue;
                            if(isset($item['id']) && $item['id']){
                                $_department1 = CompanyDepartment::find($item['id']);
                                $_department1->fill($item);
                                $_department1->save();
                                $departments_ids[] = $_department1->id;
                            }else{
                                $item['company_id'] = $company_id;
                                $item['pid'] = $_department->id;
                                $item['level'] = 2;
                                $obj = CompanyDepartment::create($item);
                                $departments_ids[] = $obj->id;
                            }
                        }
                    }
                    $departments_ids[] = $department['id'];
                }else{
                    $department['company_id'] = $company_id;
                    $obj = CompanyDepartment::create($department);
                    if(isset($department['children']) && count($department['children'])>0){
                        foreach ($department['children'] as $item) {
                            if(!$item['name'])
                                continue;
                            $item['company_id'] = $company_id;
                            $item['pid'] = $obj->id;
                            $item['level'] = 2;
                            $obj1 = CompanyDepartment::create($item);
                            $departments_ids[] = $obj1->id;
                        }
                    }
                    $departments_ids[] = $obj->id;
                }
            }
            CompanyDepartment::where('company_id', $company_id)->whereNotIn('id', $departments_ids)->delete();
        }
    }

    public function handleManger($company, $email)
    {
        $user = User::where('email', $email)->where('confirmed', 1)->where('deleted', 0)->first();
        if(!$user)
            $user = User::where('email', $email)->where('deleted', 0)->first();
        if($user){
            $has = $user->companies()->where('company_id', $company->id)->first();
            if($has){
                DB::connection('musa')->table('company_user')->where('user_id', $user->id)->where('company_id', $company->id)->update(['company_role_id' => 1]);
            }else{
                $user->companies()->attach($company->id, ['company_role_id' => 1]);
            }

            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\CompanyManagerChangeEmail($user, $company));
        }else{
            $userRe = app()->build(UserRepository::class);
            $user = $userRe->generateInviteUser($email);
            $user->companies()->attach($company->id, ['company_role_id' => 1, 'is_current'=>1]);
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\CompanyManagerChangeEmail($user, $company));
        }
    }

    public function handleUser($company, $email, $roleIds=[], $department_id=null)
    {
        $user = User::where('email', $email)->where('confirmed', 1)->where('deleted', 0)->first();
        if(!$user)
            $user = User::where('email', $email)->where('deleted', 0)->first();
        if($user){
            $has = $user->companies()->where('company_id', $company->id)->first();
            if($has){
                DB::connection('musa')->table('company_user')->where('user_id', $user->id)->where('company_id', $company->id)->update(['department_id' => $department_id]);
            }else{
                $user->companies()->attach($company->id, ['department_id' => $department_id]);
            }

            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\CompanyUserChangeEmail($user, $company));
        }else{
            $userRe = app()->build(UserRepository::class);
            $user = $userRe->generateInviteUser($email);
            $user->companies()->attach($company->id, ['department_id' => $department_id, 'is_current'=>1]);
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\CompanyUserChangeEmail($user, $company));
        }
        CompanyUserRole::where('company_id', $company->id)->where('user_id', $user->id)->delete();
        foreach ($roleIds as $roleId) {
            if($roleId==1)
                continue;
            if($roleId){
                CompanyUserRole::create([
                    'company_id'=>$company->id,
                    'user_id'=>$user->id,
                    'role_id'=>$roleId,
                ]);
                DB::connection('musa')->table('company_user')->where('user_id', $user->id)->where('company_id', $company->id)->update(['company_role_id' => $roleId]);
            }
        }
        return $user;
    }
}
