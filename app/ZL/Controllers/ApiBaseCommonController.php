<?php

namespace App\ZL\Controllers;

use App\ZL\Library\Context;
use App\ZL\Library\ErrorCode;
use App\ZL\Traites\ControllerCRUDORG;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Http\Controllers\API\CommonController;

class ApiBaseCommonController extends CommonController
{
    //使用控制器crud拓展
    use ControllerCRUDORG;


    /*---

    子类必须定义的属性或者方法
    $model_name          //模型的类 命名空间全名

    子类可以选择定义的属性或者方法
    $model_load_array    //模型可以加载的扩展属性
    authLimit()          //从权限上的限制

    ---*/


    protected $model_name;
    protected $model_load_array;

    public function getModel()
    {
        return new $this->model_name;
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $model = $this->getModel();
        //判断是否有表单验证 并且验证
        if(method_exists($this,'storeValidate')){
            $validatorArr = $this->storeValidate();
            if(count($validatorArr)>1){
                $validator = app('validator')->make($request->all(),$validatorArr[0],$validatorArr[1]);

                if($validator->fails()){
                    $errors = $validator->errors();
                    return responseZK(ErrorCode::$fieldError['code'],[],implode(',',$errors->all()));
                }
            }
        }
        if(method_exists($this,'checkStore')){
            $_ok = $this->checkStore($request);
            if($_ok)
                return responseZK(ErrorCode::$fieldError['code'],null,$_ok);
        }

        app('db')->beginTransaction();
        $requestData = $request->all();
        if(method_exists($this,'beforeStore')) {
            try {
                $res = $this->beforeStore($requestData);
                if($res)
                    $requestData = $res;
            } catch (\Exception $e) {
                app('db')->rollBack();
                return responseZK(9999, null, $e->getMessage());
            }
        }
        //添加数据
        if(!env('APP_DEBUG')){
            try {
                $obj = $model->create($requestData);
            } catch (\Exception $e) {
                app('db')->rollBack();
                return responseZK(9999,null,'保存出错');
            }
        }else{
            $obj = $model->create($requestData);
        }
        if($obj){
            if(method_exists($this,'afterStore')){
                try {
                    $res = $this->afterStore($obj,$requestData);
                } catch (\Exception $e) {
                    app('db')->rollBack();
                    return responseZK(9999,null,$e->getMessage());
                }
                if(isset($res['code']) && $res['code']==0){
                    app('db')->commit();
                    return response()->json($res);
                }else{
                    app('db')->rollBack();
                    return response()->json($res);
                }
            }else{
                app('db')->commit();
                return responseZK(0, $obj,'添加成功');
            }
        }else{
            app('db')->rollBack();
            return responseZK(ErrorCode::$modelSaveError['code'],null,ErrorCode::$modelSaveError['msg']);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id,Request $request)
    {
        if(method_exists($this,'checkShow')) {
            $res = $this->checkShow($id,$request);
            if(is_string($res)){
                responseZK(0,null,$res);
            }
        }
        Context::set(['id'=>$id]);
        $data = $this->modelPipeline([
//            'modelGetAuthLimit',
            'modelFindById',
            'modelFindLoads',
            'modelByAfterFind',
        ]);

        return responseZK(0,$data);
    }

    public function find(Request $request)
    {
        $data = $this->modelPipeline([
            'modelGetAuthLimit',
            'modelGetSearch',
            'modelGetSort',
            'modelFind',
            'modelFindLoads',
            'modelByAfterFind',
        ]);
        if($data) {
            return responseZK(0, $data);
        }else{
            return responseZK(ErrorCode::$modelCanNotFindError['code'], [],ErrorCode::$modelCanNotFindError['msg']);
        }
    }

    public function index(Request $request)
    {

        if(method_exists($this,'checkIndex')) {
            $res = $this->checkIndex($request);
            if(is_string($res)){
                responseZK(0,null,$res);
            }
        }
        $model = $this->modelPipeline([
            'modelGetAuthLimit',
            'modelGetSearch',
            'modelGetSort',
        ]);
        $model_data = clone $model;
        $count = $model->count();
        $list = $this->modelPipeline([
            'modelGetPageData',
            'collectionGetLoads',
            'modelByAfterGet',
        ],$model_data);


        $pageSize = app('request')->get('pageSize',10);
        $pagination = app('request')->get('pagination',1);
        $pagination = $pagination>0?$pagination:1;

        return $this->apiReturnJson(0, $list,'',['count'=>$count,'pageSize'=>$pageSize,'pagination'=>$pagination]);
//        return responseZK(1,$list,'',['count'=>$count,'pageSize'=>$pageSize,'pagination'=>$pagination]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $model = $this->getModel();
        //判断是否有表单验证 并且验证
        if(method_exists($this,'updateValidate')){
            $validatorArr = $this->updateValidate($id);
            if(count($validatorArr)>1){
                $validator = app('validator')->make($request->all(),$validatorArr[0],$validatorArr[1]);

                if($validator->fails()){
                    $errors = $validator->errors();
                    return responseZK(ErrorCode::$fieldError['code'],[],implode(',',$errors->all()));
                }
            }
        }

        if(method_exists($this,'checkUpdate')){
            $_ok = $this->checkUpdate($id,$request);
            if($_ok)
                return responseZK(ErrorCode::$fieldError['code'],null,$_ok);
        }

        //添加数据
        $only = isset($this->updateField)?$this->updateField:$model->fillable;

        app('db')->beginTransaction();

        try {
            $obj = $model->find($id);
            if($obj){
                $obj->fill($request->only($only));
                $ok = $obj->save();
            }else{
                return responseZK(9999, null, '保存出错');
            }
        } catch (\Exception $e) {
            app('db')->rollBack();

            if(env('APP_DEBUG')) {
                throw $e;
            }
            return responseZK(9999, null, '保存出错');
        }
        if($ok){
            if(method_exists($this,'afterUpdate')){
                try {
                    $res = $this->afterUpdate($id, $request->all(), $obj);
                } catch (\Exception $e) {
                    app('db')->rollBack();
//                    throw $e;
                    return responseZK(9999,null,$e->getFile().' '.$e->getLine().' '.$e->getMessage());
                }
                if(isset($res['code']) && $res['code']==0){
                    app('db')->commit();
                    return response()->json($res);
                }else{
                    app('db')->rollBack();
                    return response()->json($res);
                }
            }else{
                app('db')->commit();
                return responseZK(0);
            }
        }else{
            app('db')->rollBack();
            return responseZK(ErrorCode::$modelSaveError['code'],$model->where('id', '=', $id)->first()->toArray(),ErrorCode::$modelSaveError['msg']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $model = $this->getModel();
        $model->destroy($id);
        return responseZK(0);
    }

}
