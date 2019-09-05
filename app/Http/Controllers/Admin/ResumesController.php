<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecruitResume;
use App\Models\Resume;
use App\Repositories\RecruitResumesRepository;
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

        $matching = null;
        $recruit_resume_id = request('recruit_resume_id');
        if($recruit_resume_id){
            $recruitResume = RecruitResume::find($recruit_resume_id);
            if($recruitResume){
                $matching = app()->build(RecruitResumesRepository::class)->matching($recruitResume);
            }
        }
        foreach ($data['companies'] as &$company) {
            $company['job_desc'] = str_replace("\n","<br/>", $company['job_desc']);
//            $job_desc = $company['job_desc'];
//            dump($job_desc);
//            $job_descs = explode("\n", $job_desc);
//            dump($job_descs);
//            $job_descs = explode("\r\n", $job_desc);
//            dump($job_descs);
        }
//        dd($data);

        return view('resume', ['data'=>$data, 'matching'=>$matching]);
    }

    public function dumpPdf($id)
    {
        $recruit_resume_id = request('recruit_resume_id');
        global $CFG;
        requireMoodleConfig();
        $moodleRoot = getMoodleRoot();
        $resume = Resume::find($id);
        set_time_limit(0);
        $dir = $CFG->dataroot. '/pdf_files'; //放置pdf文件夹
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777, true)) { //创建和写入权限
                echo '文件夹创建失败';
                exit;
            };
        }
        $html = env("APP_URL")."/resume/{$id}?recruit_resume_id={$recruit_resume_id}"; //需要导出pdf地址
        $pdfName = date("YmdHis").rand(1000,9999) . '.pdf';
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
        upload($path, $resume->name.'的简历.pdf');
    }
}
