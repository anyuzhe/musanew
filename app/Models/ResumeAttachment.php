<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeAttachment extends Model
{
    protected $table = 'resume_attachment';

    protected $connection = 'musa';
    public $fillable = [
        'resume_id',
        'file_name',
        'file_path',
        'creator_id',
    ];
}
