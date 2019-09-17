<?php

namespace App\Console\Commands;

use App\Models\Industry;
use App\ZL\ORG\Excel\ExcelHelper;
use Illuminate\Console\Command;

class ExcelTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'excel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
//    public function __construct()
//    {
//        parent::__construct();
//    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $dir = dirname(__FILE__);
        $file = $dir.'/../../../'.'1234.xlsx';
//        $excel = new ExcelHelper();
        $arr = ExcelHelper::getArr($file);

        $level1 = null;
        foreach ($arr as $v) {
            if($v["行业类型"]){
                $level1 = Industry::create([
                    'name'=>$v["行业类型"],
                    'pid'=>0,
                    'level'=>1
                ]);
            }
            Industry::create([
                'name'=>$v["行业"],
                'pid'=>$level1->id,
                'level'=>2
            ]);
        }
    }
}
