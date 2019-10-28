<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeTrain extends Model
{
    protected $table = 'resume_train';

    protected $connection = 'musa';
    public $timestamps = false;
    public $fillable = [
        'resume_id',
        'start_date',
        'end_date',
        'organization_name',
        'train_content',
        'train_result',
    ];
}
