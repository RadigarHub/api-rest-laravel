<?php

namespace App\Http\Middleware;

use Closure;

class Cors
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

        if ($request->getMethod() === "OPTIONS"){
            return response('')
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
        }

        // Cabeceras HTTP para permitir el acceso CORS desde otro servidor web.
        return $next($request)
            // Url a la que se le permite el acceso en las peticiones
            ->header('Access-Control-Allow-Origin', '*')
            // Métodos a los que se le permite el acceso en las peticiones
            ->header('Access-Control-Allow-Headers', 'X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization')
            // Headers de la petición
            ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE')
            ->header('Allow', 'GET, POST, OPTIONS, PUT, DELETE');

    }
}
