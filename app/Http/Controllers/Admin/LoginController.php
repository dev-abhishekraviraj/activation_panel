<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;


class LoginController extends Controller
{
    public function login(Request $request){
        if($request->isMethod('POST')){
            $request->validate([
                'email'=>'required|email',
                'password'=>'required'
            ]);

           if(!\Auth::attempt($request->only('email','password'))){
                 return redirect()->back()->withErrors(['auth_error'=>'Invalid username or password']);
            }else{
                 return redirect(route('admin-dashboard'));
            }
        }
        return Inertia::render('Admin/Login');
    }

    public function logout(Request $request){
        \Auth::logout();
         return redirect('admin')->with('success','Loggedout successfully');
    }
}
