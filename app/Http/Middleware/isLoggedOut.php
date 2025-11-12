<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class isLoggedOut 
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        if (\Auth::user()) {
            if($role == 0 && \Auth::user()->is_admin == 0){
                return redirect()->route('admin-dashboard');
            }else if($role == 1 && \Auth::user()->is_admin == 1){
                return redirect()->route('client-playlist-list');
            }else if($role == 1 && \Auth::user()->is_admin == 0){
                return redirect()->route('admin-dashboard');
            }else if($role == 0 && \Auth::user()->is_admin == 1){
                return redirect()->route('client-playlist-list');
            }
            
        }

        return $next($request);
        
    }
}
