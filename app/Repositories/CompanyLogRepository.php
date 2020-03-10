<?php

namespace App\Repositories;

use App\Models\CompanyLog;

class CompanyLogRepository
{
    public static function addLog($module, $operation, $content)
    {
        CompanyLog::create([
            'company_id'=>TokenRepository::getCurrentCompany()->id,
            'user_id'=>TokenRepository::getUser()->id,
            'operation'=>$operation,
            'content'=>$content,
            'module'=>$module,
        ]);
    }

    public static function generateContent($type,$content)
    {
        $text = '';
        if($type=='add' || $type=='create'){
            $text.= '新增 ';
        }elseif($type=='edit' || $type=='update'){
            $text.= '编辑';
        }elseif($type=='delete' || $type=='del'){
            $text.= "$content 被删除";
        }

        return $text;
    }

    public static function getDiffText($obj, $classMap=null)
    {
        $text = '';
        $diff = array_diff_assoc($obj->getOriginal(), $obj->getAttributes());
        foreach ($diff as $key=>$value) {
            $_v1 = self::getOptionsText($key, $value);
            $_v2 = self::getOptionsText($key, $obj->{$key});
            if($classMap){
                $_v1 = $classMap::changeValue($key, $_v1);
                $_v2 = $classMap::changeValue($key, $_v2);
            }
            $text.= self::translation($key).": {$_v1} 修改成 {$_v2}, ";
        }
        return substr($text,0,strlen($text)-2);
    }

    public static function translation($str)
    {
        if(isset(self::$translationData[$str])){
            return self::$translationData[$str];
        }
        $text = '';
        $strArr = explode('_',$str);
        foreach ($strArr as $item) {
            if(isset(self::$translationData[$item])){
                $text.= self::$translationData[$item];
            }else{
                $text.= $item;
            }
        }
        return $text;
    }

    public static function getOptionsText($key, $value){
        global $options;
        if(!$options){
            $options = \App\Models\DataMap::get(['id','name']);
            $options->load('options');
            $options = $options->keyBy('name');
        }
        $has = $options->get($key);
        if($has){
            $_ops = $has->options->keyBy('value')->toArray();

            if(isset($_ops[$value])){
                return $_ops[$value]['text'];
            }
        }
        if($value===false){
            return '否';
        }
        if($value===true){
            return '是';
        }
        return $value;
    }


    protected static $translationData = [
      'name'=>'名称',
      'code'=>'编号',
      'major'=>'专业',
      'requirements'=>'要求',
      'salary'=>'薪资',
      'min'=>'最小',
      'max'=>'最大',
      'occupation'=>'职业',
      'work'=>'工作',
      'nature'=>'性质',
      'status'=>'状态',
      'description'=>'描述',
      'years'=>'年数',
      'educational'=>'学历',
      'is'=>'是否',
      'formal'=>'正式',
      'resume'=>'简历',
      'grade'=>'分数',
      'setting'=>'设置',
      'need'=>'需要',
      'num'=>'数量',
      'public'=>'公开',
      'leading'=>'负责人',
      'id'=>'',

        //resume
        'start'=>'开始',
        'at'=>'时间',
        'workplace'=>'期望工作地点',
        'job'=>'求职',
        'hope'=>'期望',
        'intro'=>'个人评价',
        'phone'=>'手机',
        'married'=>'结婚',
        'gender'=>'性别',
        'birthdate'=>'生日',
        'avatar'=>'头像',

        //company
        'company'=>'公司',
        'alias'=>'简称',
        'scale'=>'规模',
        'industry'=>'行业',
        'conglomerate'=>'集团',
        'third'=>'第三',
        'party'=>'方',
        'demand'=>'需求',
        'side'=>'方',
        'suspended'=>'暂停',
        'province'=>'省',
        'city'=>'市',
        'district'=>'区',
        'site'=>'地址',
        'deposit'=>'开户',
        'bank'=>'银行',
        'tax_no'=>'税号',
        'account'=>'账号',
        'contact'=>'联系人',
    ];
}
