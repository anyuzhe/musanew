<?php

namespace App\Repositories;

use App\Models\CompanyAddress;
use App\Models\CompanyDepartment;

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
                    CompanyAddress::where('id', $address['id'])->update($address);
                }else{
                    $obj = CompanyAddress::create($address);
                    $addresses_ids[] = $obj->id;
                }
            }
            CompanyAddress::where('company_id', $company_id)->whereNotIn('id', $addresses_ids)->delete();
        }
        if($departments && is_array($departments)){
            $departments_ids = [];
            foreach ($departments as $department) {
                $department['company_id'] = $company_id;
                if(isset($department['id']) && $department['id']){
                    $departments_ids[] = $department['id'];
                    CompanyDepartment::where('id', $department['id'])->update($department);
                }else{
                    $obj = CompanyDepartment::create($department);
                    $departments_ids[] = $obj->id;
                }
            }
            CompanyDepartment::where('company_id', $company_id)->whereNotIn('id', $departments_ids)->delete();
        }
    }
}
