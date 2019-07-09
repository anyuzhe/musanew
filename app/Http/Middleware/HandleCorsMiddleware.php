<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class HandleCorsMiddleware
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
        if($request->getMethod() === 'OPTIONS'){

            $response = new Response();
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', "GET, POST, PUT, DELETE, PATCH, OPTIONS");
            $response->headers->set('Access-Control-Allow-Headers', 'X-Token,Token,token,x-requested-with,content-type,session-id,remember-token,x-csrf-token');

            return $response;
        }else{
//            if(isset($_SERVER['HTTP_ORIGIN']) || isset($_SERVER['APP_URL'])){
//                $origin = isset($_SERVER['HTTP_ORIGIN'])?$_SERVER['HTTP_ORIGIN']:$_SERVER['APP_URL'];
//                header("Access-Control-Allow-Origin: {$origin}");//APP_URL
//            }else{
//                header("Access-Control-Allow-Origin: *");
//            }
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Credentials: true");
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
            header('Access-Control-Allow-Headers:X-Token,Token,token,x-requested-with,content-type,session-id,remember-token,x-csrf-token');
        }
        return $next($request);
    }
}
