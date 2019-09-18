<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestUploadResume extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:resume';

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
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $file = Storage::disk(config('voyager.storage.disk'))->get('/resumes/doc.NO.4.docx');
        $data = [
            'filename'=>'doc.NO.4.docx',
            'content'=>base64_encode((string)$file),
            'need_avatar'=>0
        ];
        $headers = [
            'X-API-KEY: izrNtgTds8XEi3fwvJu88klg6X9Im9Jx'
        ];
        $url = "https://www.belloai.com/v2/open/resume/parse";
        $res = http_post_json($url, json_encode($data, 256) ,$headers);
        if($res[0]=='200'){
            $array = json_decode($res[1], true);
            dd($array);
        }
        dd($res);
    }
}
