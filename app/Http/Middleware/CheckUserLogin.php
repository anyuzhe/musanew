<?php

namespace App\Http\Middleware;

use App\Models\ExternalToken;
use App\ZL\ResponseLayout;
use Closure;

class CheckUserLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->get('token');
        if(!$token)
            $token = $request->header('token');
        if(!$token)
            $token = $request->header('Token');
        $tokenModel = ExternalToken::where('token', $token)->first();
        if($tokenModel){
            global $LOGIN_USER;
            $LOGIN_USER = $tokenModel->user;
        }else{
            return ResponseLayout::apply(999);
        }
        return $next($request);
    }
}
