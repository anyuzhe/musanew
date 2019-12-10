<?php

namespace App;

use App\Models\Admin\FrontMenuItem;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use TCG\Voyager\Traits\VoyagerUser;

class User extends \TCG\Voyager\Models\User
{
    use Notifiable;
    use VoyagerUser;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function permissions()
    {
        $this->loadPermissionsRelations();

        $_permissions = $this->roles_all()
            ->pluck('permissions')->flatten();

        return $_permissions;
    }

    public function getFrontMenuList()
    {
        $type0List = FrontMenuItem::where('type', 0)->orderBy('order')->get();
        $type0List->load('children');
        return $type0List;
    }
}
