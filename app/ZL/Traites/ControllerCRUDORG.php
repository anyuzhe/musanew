<?php
/**
 * Created by PhpStorm.
 * User: anyuzhe
 * Date: 2017/4/1
 * Time: 10:04
 */

namespace App\ZL\Traites;


use App\ZL\Library\Context;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

trait ControllerCRUDORG
{

    //----------------------------辅助方法-------------------------------

    //管道方法 进行模型处理过程
    protected function modelPipeline(array $pipes,$model=false)
    {
        $model = !$model?$this->getModel():$model;
        foreach ($pipes as $pipe) {
            if(method_exists($this, $pipe)){
                $model = $this->$pipe($model);
            }
        }
        return $model;
    }


    //判断和应用子类中的限制
    protected function modelGetAuthLimit(&$model)
    {
        if(method_exists($this,'authLimit')){
            $where = $this->authLimit($model);
            if($where){
                $model = $model->where($where);
            }
        }
        return $model;
    }

    //获取 并且 搜索
    protected function modelGetSearch(&$model)
    {
        $groups = [];
        if(isset($this->search_field_array)){
            foreach ($this->search_field_array as $item) {
                if(is_array($item)){
                    $_name = $item[0];
                    $_action = $item[1];
                    $_str = app('request')->get($_name,null);
                    if(!$this->isEmpty($_str)){
                        if(isset($item[2])){
                            $group = $item[2];
                            if(!isset($groups[$group]))
                                $groups[$group] = [];
                            $item['value'] = $_str;
                            $groups[$group][] = $item;
                        }else{
                            if($_action=='like'){
                                $model = $model->where($_name, $_action, "%$_str%");
                            }else{
                                $model = $model->where($_name, $_action, $_str);
                            }
                        }
                    }
                }else{
                    $_str = app('request')->get($item,null);
                    if($_str){
                        $model = $model->where($item,$_str);
                    }
                }
            }
        }
        foreach ($groups as $group) {
            $model = $model->where(function ($query)use($group){
                $_first = true;
                foreach ($group as $item) {
                    $_name = $item[0];
                    $_action = $item[1];
                    $_str = $item['value'];
                    if(!$this->isEmpty($_str)){
                        if($_first){
                            $_first = false;
                            if($_action=='like'){
                                $query->where($_name, $_action, "%$_str%");
                            }else{
                                $query->where($_name, $_action, $_str);
                            }
                        }else{
                            if($_action=='like'){
                                $query->orWhere($_name, $_action, "%$_str%");
                            }else{
                                $query->orWhere($_name, $_action, $_str);
                            }
                        }
                    }
                }
            });
        }
        return $model;
    }

    protected function isEmpty($_str)
    {
        if($_str)
            return false;
        if(is_null($_str))
            return true;
        if(is_int($_str))
            return false;
        if(is_string($_str) && strlen($_str)==0)
            return true;
        return false;
    }

    //排序
    protected function modelGetSort(&$model)
    {
        $sortBy = app('request')->get('sortBy',false);
        $orderBy = app('request')->get('orderBy','desc');

        $model = $model->when($sortBy, function ($query) use ($sortBy,$orderBy){
            return $query->orderBy($sortBy,$orderBy);
        }, function ($query) use ($orderBy){
            return $query->orderBy('id',$orderBy);
        });
        return $model;
    }

    //根据分页 获取内容
    protected function modelGetPageData($model)
    {
        $pageSize = app('request')->get('pageSize',10);
        $pagination = app('request')->get('pagination',1);
        $pagination = $pagination>0?$pagination:1;
        //分页获取信息
        $list = $model->skip($pageSize*($pagination-1))->take($pageSize)->get();
        return $list;
    }

    //获取单个
    protected function modelFind($model)
    {
        return $model->first();
    }
    //获取单个根据id
    protected function modelFindById($model)
    {
        return $model->find(Context::get('id'));
    }

    //模型集合获取关联
    protected function collectionGetLoads(Collection &$list)
    {
        $req = app('request')->get('loads','');
        $loads = $req?explode(',', $req):[];
        //加载关联
        if($list->count() && $loads && $this->model_load_array){
            foreach ($loads as $load) {
                if(in_array(lcfirst($load),$this->model_load_array)){
                    $list->load($load);
                }
            }
        }
        return $list;
    }

    //模型获取关联
    protected function modelFindLoads($data)
    {
        $req = app('request')->get('loads','');
        $loads = $req?explode(',', $req):[];

        if($loads && $this->model_load_array && $data){
            //加载关联
            foreach ($loads as $load) {
                if (in_array(lcfirst($load), $this->model_load_array)) {
                    $data->$load;
                }
            }
        }
        return $data;
    }

    //模型集合后置转换
    protected function modelByAfterGet(Collection &$list)
    {
        //判断和应用子类中的字段转换
        if(method_exists($this,'_after_get') && $list->count()) {
            $list = $this->_after_get($list);
        }
        return $list;
    }
    //模型集合后置转换
    protected function modelByAfterFind($data)
    {
        //判断和应用子类中的字段转换
        if(method_exists($this,'_after_find') && $data) {
            $this->_after_find($data);
        }
        return $data;
    }
}
