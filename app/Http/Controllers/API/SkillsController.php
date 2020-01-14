<?php

namespace App\Http\Controllers\API;

use App\Repositories\AreaRepository;
use App\Repositories\SkillsRepository;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SkillsController extends CommonController
{
    public function getTree()
    {
        $areas = SkillsRepository::getTree();
        return self::apiReturnJson(0, $areas);
    }

    public function saveTree(Request $request)
    {
        $data = $request->get('data');
        $res = SkillsRepository::saveTree($data);
        return self::apiReturnJson(0, $res);
    }
}
