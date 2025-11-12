<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class validateAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (\Auth::user() && \Auth::user()->is_admin == 0) {
            return $next($request);
        }else if(\Auth::user() && \Auth::user()->is_admin == 1){
            return redirect()->route('client-playlist-list');
        }
       
    }
}
