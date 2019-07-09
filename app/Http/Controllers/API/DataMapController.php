<?php

namespace App\Http\Controllers\API;

use App\Models\Resume;
use App\ZL\Controllers\ApiBaseCommonController;
use Illuminate\Support\Facades\DB;

class DataMapController extends ApiBaseCommonController
{

    public function extendMap()
    {
        $group = $this->request->get('group','');
        if($group)
            $maps = \App\Models\DataMap::where('group', 'like', "%$group%")->get();
        else
            $maps = \App\Models\DataMap::all();
        if (!$maps)
            return $this->apiReturnJson(9998);
        $rt = [];
        foreach ($maps as $map) {
            $d = $map->options()->get(['value', 'text'])->toArray();
            if ($d) {
                $rt[$map->name] = $d;
            }
        }
        return $this->apiReturnJson(0, $rt);
    }
}
