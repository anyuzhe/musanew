<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Voyager\VoyagerBaseController;
use App\Models\User;
use App\Models\UserBasicInfo;
use App\ZL\Moodle\UserHelper;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Events\BreadDataUpdated;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Models\DataRow;

class UsersInfoController extends VoyagerBaseController
{


    public function update(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Compatibility with Model binding.
        $id = $id instanceof Model ? $id->{$id->getKeyName()} : $id;

        $model = app($dataType->model_name);
        if ($dataType->scope && $dataType->scope != '' && method_exists($model, 'scope'.ucfirst($dataType->scope))) {
            $model = $model->{$dataType->scope}();
        }
        if ($model && in_array(SoftDeletes::class, class_uses($model))) {
            $data = $model->withTrashed()->findOrFail($id);
        } else {
            $data = call_user_func([$dataType->model_name, 'findOrFail'], $id);
        }

        // Check permission
        $this->authorize('edit', $data);

        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->editRows, $dataType->name, $id)->validate();
        $data = $this->insertUpdateData($request, $slug, $dataType->editRows, $data);

        $realname = $request->get('realname');
        User::where('id', $data->user_id)->update([
            'firstname'=>$realname?substr_text($realname,0,1):'',
            'lastname'=>$realname?substr_text($realname,1, count($realname)):'',
        ]);

        event(new BreadDataUpdated($dataType, $data));

        return redirect()
            ->route("voyager.user.index")
            ->with([
                'message'    => __('voyager::generic.successfully_updated')." {$dataType->display_name_singular}",
                'alert-type' => 'success',
            ]);
    }
}
