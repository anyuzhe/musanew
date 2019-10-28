<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeRain extends Model
{
    protected $table = 'resume_rain';

    protected $connection = 'musa';
    public $timestamps = false;
    public $fillable = [
        'resume_id',
        'start_date',
        'end_date',
        'organization_name',
        'rain_content',
        'rain_result',
    ];


//$table->integer('resume_id')->comment('简历id');
//$table->string('start_date', 191)->nullable()->comment('开始时间');
//$table->string('end_date', 191)->nullable()->comment('结束时间');
//$table->string('organization_name', 191)->nullable()->comment('机构名称');
//$table->text('rain_content')->nullable()->comment('培训内容');
//$table->string('rain_result', 191)->nullable()->comment('培训成果');
}
