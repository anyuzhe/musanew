<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyLog extends Model
{
    protected $table = 'company_logs';

    protected $connection = 'musa';
    public $fillable = [
        'company_id',
        'user_id',
        'operation',
        'content',
        'module',
    ];

    public function user()
    {
        return $this->belongsTo(UserBasicInfo::class, 'user_id', 'user_id');
    }
}
