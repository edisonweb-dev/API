<?php

namespace App\Http\Middleware;

use Closure;

class prueba
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
        
        if($request->validar != 1 ){
            echo "no puedes acceder";
        }

        return $next($request);  
        
    }
}
