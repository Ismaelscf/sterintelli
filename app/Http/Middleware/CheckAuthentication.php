<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class CheckAuthentication
{
    public function handle($request, Closure $next)
    {
        if (Session::has('user')) {
            $userCodigo = Session::get('user');
            
            return $next($request);
        }

        return redirect('/login')->withErrors(['Por favor, faça login para acessar esta página.']);
        
    }
}
