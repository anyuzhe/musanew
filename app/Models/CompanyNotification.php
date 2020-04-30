<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyNotification extends Model
{
    protected $table = 'company_notifications';

    protected $connection = 'musa';
    public $fillable = [
        'company_id',
        'type',
        'content',
        'other_data',
        'is_read',
        'read_at',
    ];
}
