<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Traits\Translatable;

class FrontMenuItem extends Model
{
    protected $table = 'front_menu_items';

    protected $guarded = [];

    protected $translatable = ['title'];

    public function children()
    {
        return $this->hasMany(FrontMenuItem::class, 'parent_id');
//            ->with('children');
    }
}
