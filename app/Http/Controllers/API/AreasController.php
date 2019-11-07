<?php

namespace App\Http\Controllers\API;

use App\Models\Recruit;
use App\Repositories\AreaRepository;
use DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AreasController extends CommonController
{
    public function getTree()
    {
        set_time_limit(0);

        $areas = Cache::remember('areaData', 3600*24, function () {
            return AreaRepository::getTree();
        });

        return $this->apiReturnJson(0, $areas);
    }

    public function getHotCity()
    {
        $data = DB::connection('musa')
            ->table('company_job_recruit')
            ->select(DB::raw('count(*) as num , mdl_musa_area.id, mdl_musa_area.cname'))
            ->leftJoin('jobs', 'company_job_recruit.job_id', '=', 'jobs.id')
            ->leftJoin('company_addresses', 'jobs.address_id', '=', 'company_addresses.id')
            ->leftJoin('area', 'company_addresses.city_id', '=', 'area.id')
            ->groupBy('area.id')
            ->whereNotNull('company_addresses.city_id')
            ->where('company_job_recruit.is_public', 1)
            ->orderBy('num', 'desc')
            ->get();

        return $this->apiReturnJson(0, $data);
    }
}
