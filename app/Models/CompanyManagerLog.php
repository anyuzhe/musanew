<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyManagerLog extends Model
{
    protected $table = 'company_manager_logs';

    protected $connection = 'musa';
    public $fillable = [
        'company_id',
        'new_id',
        'old_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'new_id');
    }
}
