<?php

namespace App\Http\Controllers\API;

use App\Repositories\AreaRepository;
use DB;
use Illuminate\Support\Facades\Log;

class AreasController extends CommonController
{
    public function getTree()
    {
        set_time_limit(0);
        $areas = AreaRepository::getTree();
        return self::apiReturnJson(0, $areas);
    }
}
