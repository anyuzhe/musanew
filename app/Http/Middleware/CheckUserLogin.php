<?php

namespace App\Http\Middleware;

use App\Models\ExternalToken;
use App\Repositories\TokenRepository;
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
        $tokenModel = TokenRepository::getTokenModel();
        if(!$tokenModel){
            return ResponseLayout::apply(999);
        }
        return $next($request);
    }
}
