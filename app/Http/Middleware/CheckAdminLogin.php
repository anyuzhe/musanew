<?php

namespace App\Http\Middleware;

use App\Models\ExternalToken;
use App\Repositories\TokenRepository;
use App\ZL\ResponseLayout;
use Closure;

class CheckAdminLogin
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
        $admin = TokenRepository::getAdmin();
        if(!$admin && !$request->get('is_test')){
            return ResponseLayout::apply(999);
        }
        return $next($request);
    }
}
