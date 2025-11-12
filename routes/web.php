<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\MacDeviceController;
use App\Http\Controllers\Admin\PlaylistController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Api\ApiClientController;
use App\Http\Controllers\FileUploadController;



//Admin routes
Route::group(['prefix'=>'admin'],function(){

        Route::middleware(['isLoggedOut:0'])->group(function(){
                Route::any('/',[LoginController::class,'login'])->name('admin-login');
        });

        Route::middleware(['isLoggedIn:0','validateAdminRole'])->group(function(){

            Route::get('dashboard',[DashboardController::class,'dashboard'])->name('admin-dashboard');

            //Playlist routes
            Route::get('playlist/list',[PlaylistController::class,'index'])->name('admin-playlist-list');
            Route::any('playlist/create',[PlaylistController::class,'create_playlist'])->name('admin-playlist-create');
            Route::any('playlist/edit/{id?}',[PlaylistController::class,'edit_playlist'])->name('admin-playlist-edit');
            Route::post('password/check',[PlaylistController::class,'check_password'])->name('admin-password-check');
            Route::post('playlist/status',[PlaylistController::class,'manage_status'])->name('admin-playlist-status');
            Route::get('file/download/{filename}',[PlaylistController::class,'download_file'])->name('admin-file-download');
            Route::post('playlist/delete',[PlaylistController::class,'delete_playlist'])->name('admin-playlist-delete');

            //Log routes
            Route::get('log/list',[LogController::class,'index'])->name('admin-log-list');
            Route::post('log/status',[LogController::class,'manage_status'])->name('admin-log-status');
            Route::post('log/delete',[LogController::class,'delete_log'])->name('admin-log-delete');


            //Logout
            Route::post('logout',[LoginController::class,'logout'])->name('admin-logout');

            Route::post('file/upload',[FileUploadController::class,'fileUpload'])->name('admin-file-upload');
        });
});


Route::middleware(['isLoggedOut:1'])->group(function(){
  Route::any('/',[HomeController::class,'login'])->name('client-login');
});


Route::middleware(['isLoggedIn:1','validateClientRole'])->group(function(){
  
  Route::get('playlist/list',[HomeController::class,'list_playlist'])->name('client-playlist-list');
  Route::any('playlist/register',[HomeController::class,'index'])->name('client-playlist-register');
  Route::any('upload/m3u',[FileUploadController::class,'upload'])->name('client-m3u-upload');
  Route::any('playlist/edit/{id?}',[HomeController::class,'edit_playlist'])->name('client-playlist-edit');
  Route::get('file/download/{filename}',[HomeController::class,'download_file'])->name('client-file-download');
  Route::post('playlist/delete',[HomeController::class,'delete_playlist'])->name('client-playlist-delete');

  Route::post('password/change',[HomeController::class,'change_password'])->name('client-password-change');
  Route::post('password/validate',[HomeController::class,'validate_password'])->name('client-password-validate');

  Route::post('logout',[HomeController::class,'logout'])->name('client-logout');

  Route::post('file/upload',[FileUploadController::class,'fileUpload'])->name('client-file-upload');

});



Route::any('login_qr/{qr_login_token?}', [HomeController::class, 'login_qr']);




