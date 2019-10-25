<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Connectors\ConnectionFactory;

class TestDBSchema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tdbs';

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
        $app = app();
        $connect = $app['db']->makeConnection('musa');
        $connect->useDefaultSchemaGrammar();
        $s = new \Illuminate\Database\Schema\Builder($connect);
        dd($s->hasTable('resumes'));
    }
}
