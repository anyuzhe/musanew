<?php

namespace App;

use App\Models\Admin\FrontMenuItem;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
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

    public function getAllFrontMenuList()
    {
        $all = FrontMenuItem::where('type', 0)->orderBy('order')->get();
        $all->load('children');
        foreach ($all as &$item) {
            foreach ($item->children as &$child) {
                $child->parent_title = $item->title;

                foreach ($child->children as &$childd) {
                    $childd->parent_title = $child->title;
                }
            }
            $item->parent_title = '';
        }
        return $all;
    }

    public function getFrontMenuList()
    {
        $roleIds = $this->roles_all()->pluck('id');
        $menuIds = DB::table('front_menu_role')->where('role_id', $roleIds)->pluck('menu_id');
        $all = FrontMenuItem::where('type', 0)->whereIn('id', $menuIds)->orderBy('order')->get();
        $all->load('children');
        foreach ($all as &$item) {
            foreach ($item->children as &$child) {
                $child->parent_title = $item->title;
//                foreach ($child->children as &$childd) {
//                    $childd->parent_title = $child->title;
//                }
            }
            $item->parent_title = '';
        }
        return $all;
    }

    public function frontPermissions()
    {
        $isAdmin = DB::table('user_roles')->where('user_id', $this->id)->where('role_id', 1)->first();
        if($isAdmin || $this->role_id==1)
            return FrontMenuItem::all()->pluck('auth_key')->toArray();
        $this->loadMenusRelations();
//        dd($this->roles_all()
//            ->pluck('frontMenus'));
        $_permissions = $this->roles_all()
            ->pluck('frontMenus')->flatten()
            ->pluck('auth_key')->unique()->toArray();
        foreach ($_permissions as $k=>$v) {
            if(!$v)
                unset($_permissions[$k]);
        }
        return array_values($_permissions);
    }

    public function loadMenusRelations()
    {
        $this->loadRolesRelations();

        if ($this->role && !$this->role->relationLoaded('frontMenus')) {
            $this->role->load('frontMenus');
            $this->load('roles.frontMenus');
        }
    }
}
