<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */

    protected $addHttpCookie = true;


    protected $except = [
        //
        '/tareas/guardar',
        '/tareas/actualizar',
        '/tareas/borrar/*',
        '/tareas/validar',
        '/token/login',
        '/token/me',
        '/token/refresh',
        '/tokens/register/',
        '/tokens/login/',
        '/tokens/open/',
        '/tokens/user/',
        '/tokens/closed/',
        
        
    ];
}
