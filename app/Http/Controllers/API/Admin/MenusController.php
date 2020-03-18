<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\Admin\FrontMenuItem;
use App\Models\Company;
use App\Models\ExternalToken;
use App\Models\PasswordFindCode;
use App\User;
use App\Models\UserBasicInfo;
use App\ZL\Controllers\ApiBaseCommonController;
use App\ZL\Moodle\EmailHelper;
use App\ZL\Moodle\TokenHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MenusController extends ApiBaseCommonController
{
    protected $model_name = FrontMenuItem::class;

    public function index(Request $request)
    {
        $admin = $this->getAdmin();
        return $this->apiReturnJson(0, $admin->getAllFrontMenuList());
    }
}
