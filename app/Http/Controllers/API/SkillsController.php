<?php

namespace App\Http\Controllers\API;

use App\Repositories\AreaRepository;
use App\Repositories\SkillsRepository;
use DB;
use Illuminate\Support\Facades\Log;

class SkillsController extends CommonController
{
    public function getTree()
    {
        $areas = SkillsRepository::getTree();
        return self::apiReturnJson(0, $areas);
    }
}
