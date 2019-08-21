<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resume;
use App\Repositories\ResumesRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ResumesController extends Controller
{
    public function show($id)
    {
        $data = Resume::find($id);

        $data = app()->build(ResumesRepository::class)->getData($data);
        getOptionsText($data);
        $data = $data->toArray();
        $data['educations'] = (new Collection($data['educations']))->sortByDesc('start_date')->values()->toArray();
        $data['projects'] = (new Collection($data['projects']))->sortByDesc('project_start')->values()->toArray();
        $data['companies'] = (new Collection($data['companies']))->sortByDesc('job_start')->values()->toArray();
//        dd($data);
        return view('resume', ['data'=>$data]);
    }

    public function dumpPdf($id)
    {
        global $CFG;
        requireMoodleConfig();
        $moodleRoot = getMoodleRoot();

        set_time_limit(0);
        $dir = $CFG->dataroot. '/estimate_pdf'; //放置pdf文件夹
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777, true)) { //创建和写入权限
                echo '文件夹创建失败';
                exit;
            };
        }
        $html = env("APP_URL")."resume/{$id}"; //需要导出pdf地址
        $pdfName = date("Y-m-d_His") . '.pdf';
        $path = $dir . '/' . $pdfName;

        if (strstr(php_uname('s'), "Windows ")) {
            shell_exec(" wkhtmltopdf $html $path"); //windows  wkhtmltopdf 调用
        } else {
            shell_exec("/usr/local/bin/wkhtmltopdf $html $path"); // linux  wkhtmltopdf 调用
        }
//文件输出浏览器下载
        function upload($path, $pdfName)
        {
            if (!file_exists($path)) {
                echo '文件不存在';
                exit;
            }
            $filename = realpath($path); //文件名
            Header("Content-type:  application/octet-stream ");
            Header("Accept-Ranges:  bytes ");
            Header("Accept-Length: " . filesize($filename));
            header("Content-Disposition:  attachment;  filename= $pdfName");
            echo file_get_contents($filename);
            readfile($filename);
            unlink($path);
        }
        upload($path, $pdfName);
    }
}
