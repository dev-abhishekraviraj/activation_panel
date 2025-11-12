<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class isLoggedIn 
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        if (!\Auth::user()) {
            if($role == 0){
                return redirect()->route('admin-login')->with('auth_error','Please login');
            }else{
                return redirect()->route('client-login');
            }
            
        }
       return $next($request);
    }
}
 