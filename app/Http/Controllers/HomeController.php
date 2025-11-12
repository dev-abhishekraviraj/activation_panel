<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Playlist;
use App\Models\MacDevice;
use App\Models\M3uContent;
use App\Models\User;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Helpers\CustomEncryptor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;




class HomeController extends Controller
{

    
    public function login(Request $request){
        if($request->isMethod('POST')){
            $request->validate([
                    'mac_address' => ['required', 'regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/'],
                    'password' => 'required'
                ],[
                    'mac_address.required' => 'Mac address is required.',
                    'password.required' => 'Device key is required.'
            ]);
           $credentials['username'] = $request['mac_address'];
           $credentials['password'] = $request['password'];
           if(Auth::attempt($credentials)){
                return redirect(route('client-playlist-list'));
           }else{
                return redirect()->back()->withErrors(['auth_error'=>'Invalid username or password.']);
           }
        }
        return Inertia::render('Login');
    }

    public function login_qr(Request $request,$qr_login_token = ''){
        
        if($request->isMethod('POST')){

            if(DB::table('qr_login_tokens')->where('user_id',Auth::user()->id)->count() > 0){
                $records = DB::table('qr_login_tokens')->where('user_id',Auth::user()->id)->get();
                foreach($records as $record){
                    if (Storage::disk('public')->exists('qr-codes/' . $record->qr_login_tokens . '.png')) {
                        Storage::disk('public')->delete('qr-codes/' . $record->qr_login_tokens . '.png');
                    }
                }
                
                DB::table('qr_login_tokens')->where('user_id',Auth::user()->id)->delete();
                return 'deleted';
            }
        }

        $record = DB::table('qr_login_tokens')->where('qr_login_tokens',$qr_login_token)->first();
        if($record){
            $user = User::where('id',$record->user_id)->first();
            if($user){
                Auth::login($user);
                return redirect()->route('client-playlist-list');
            }else{
                return 'Unauthorized.';
            }
        }else{
            
            return 'Token Expired.';
        }
       
       
    }


    public function index(Request $request){
       
        if($request->isMethod('POST')){
            
            if($request['type'] == 'file'){
                $m3uUrlValidation = '';
                $m3uFileValidation = 'required|file|mimes:txt,m3u,m3u8|max:204800';
            }else if($request['type'] == 'url'){
                $m3uFileValidation = '';
                $m3uUrlValidation = 'required|url';
            }

            if($request['is_protected'] == 1){
                $passwordValidation = 'required|confirmed';
            }else{
                $passwordValidation = '';
            }

            $rules = [
                'playlist_name' => 'required',
                'm3u_file' => $m3uFileValidation,
                'm3u_url' =>  $m3uUrlValidation,
                'password' => $passwordValidation
            ];

            $error_messages = [
                'm3u_file.max' =>       'M3U file should not be more than 200MB.',
                'm3u_file.required' =>  'Please choose a file or enter a valid url.',
                'm3u_url.required' =>   'Please choose a file or enter a valid url.',
                'password.required' =>  'Password field is required.',
                'password.confirmed' => 'Password and confirm password must be same.',
            ];
           
            $request->validate($rules,$error_messages);

            if($request['type'] == 'file' && $request->hasFile('m3u_file')){
                //Check that the file contains a valid M3U header
                $fileContents = file_get_contents($request->file('m3u_file')->getPathname());
                if (strpos($fileContents, '#EXTM3U') === false) {
                    return redirect()->back()->withErrors(['m3u_file' => 'Please choose a valid m3u file.']);
                }
                
            }

            $mac_id = MacDevice::where('mac_address',Auth::user()->username)->first()->id;
            $password=($request['password'] != '') ? Hash::make($request['password']):'';
            $shareable_password = ($request['password'] != '')?CustomEncryptor::decrypt($request['password'], env('SALT')):'';

            if($request['type'] == 'file'){
                $file = $request->file('m3u_file');
                $filename = rand() . '.m3u';
                $file->move('m3u_files',$filename);
                $postData = [

                            'mac_id' => $mac_id,
                            'playlist_name' => $request['playlist_name'],
                            'stream_line' => $filename,
                            'filepath' => request()->root() . '/' . 'm3u_files/' . $filename,
                            'epg' => $request['xmltv_url'] ?? '',
                            'type' => 'file',
                            'epg_countries' => $request['epg_countries'] ?? '',
                            'logos' => $request['logos'] ?? '',
                            'save_online' => ($request['save_online'] == 1)?1:0,
                            'detect_epg' => ($request['detect_epg'] == 1)?1:0,
                            'disable_groups' => ($request['disable_groups'] == 1)?1:0,
                            'is_protected' => ($request['is_protected'] == 1)?1:0,
                            'password' => $password,
                            'shareable_password' => $shareable_password,
                            'status' => ($request['status'] == 'active')?1:0,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        Playlist::insert($postData);
                        manage_logs('Playlist create',$postData,'Playlist created successfully.',Auth::user()->id);
 
            }
            else if($request['type'] == 'url'){
            
                $postData = [
                            
                            'mac_id' => $mac_id,
                            'playlist_name' => $request['playlist_name'],
                            'stream_line' => $request['m3u_url'],
                            'epg' => $request['xmltv_url'] ?? '',
                            'type' => 'url',
                            'epg_countries' => $request['epg_countries'] ?? '',
                            'logos' => $request['logos'] ?? '',
                            'save_online' => ($request['save_online'] == 1)?1:0,
                            'detect_epg' => ($request['detect_epg'] == 1)?1:0,
                            'disable_groups' => ($request['disable_groups'] == 1)?1:0,
                            'is_protected' => ($request['is_protected'] == 1)?1:0,
                            'password' => $password,
                            'shareable_password' => $shareable_password,
                            'status' => ($request['status'] == 'active')?1:0,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        Playlist::insert($postData);
                        manage_logs('Playlist create',$postData,'Playlist created successfully.',Auth::user()->id);  

            }
            return redirect()->route('client-playlist-list')->with('success','Playlist created successfully.');
        }

        $device = $this->get_device_details();
        return Inertia::render('Home',['devicedetails'=>$device]);
    }

    public function list_playlist(){
       
        $device = $this->get_device_details();
        $records = Playlist::where('mac_id',$device->id)->orderBy('id','desc')->get()->map(function ($record) {
            return [
                'id' => $record->id,
                'stream_line' => $record->stream_line,
                'type' => $record->type,
                'is_protected' => $record->is_protected,
                'status' => $record->status,
                'created_at' => Carbon::parse($record->created_at)->format('jS F Y'),

            ];
        });
    
        return Inertia::render('PlaylistList',['records' => $records, 'devicedetails' => $device ]);
    }

    

    public function edit_playlist(Request $request,$id = ''){

        if($request->isMethod('POST')){
          
            if($request['type'] == 'file'){
                $m3uUrlValidation = '';
                $m3uFileValidation = 'nullable|file|mimes:txt,m3u,m3u8|max:204800';
            }else if($request['type'] == 'url'){
                $m3uFileValidation = '';
                $m3uUrlValidation = 'required|url';
            }

            $record = Playlist::where('id',$request['id'])->first();
            if($request['is_protected'] == 1){
                if($record->password == ''){
                  $passwordValidation = 'required|confirmed';
                }else{
                    if($request['password'] != ''){
                        $passwordValidation = 'confirmed';
                    }else{
                        $passwordValidation = '';
                    }
                }
                
            }else{
                $passwordValidation = '';
            }

            $rules = [
                'playlist_name' => 'required',
                'm3u_file' => $m3uFileValidation,
                'm3u_url' =>  $m3uUrlValidation,
                'password' => $passwordValidation
            ];

            $error_messages = [
                'm3u_file.max' =>  'M3U file should not be more than 200MB.',
                'm3u_url.required' => 'M3U url field is required.',
                'password.confirmed' => 'Password and confirm password must be same.',
            ];
           
            $request->validate($rules,$error_messages);

            if($request['type'] == 'file' && $request->hasFile('m3u_file')){
                //Check that the file contains a valid M3U header
                $fileContents = file_get_contents($request->file('m3u_file')->getPathname());
                if (strpos($fileContents, '#EXTM3U') === false) {
                    return redirect()->back()->withErrors(['m3u_file' => 'Please choose a valid m3u file.']);
                }
                
            }

            //Updation work starts here
            $mac_id = MacDevice::where('mac_address',Auth::user()->username)->first()->id;
            $record = Playlist::where('id',$request['id'])->first();
            if($request['is_protected'] == 1){
                $password=($request['password'] != '') ? Hash::make($request['password']):$record->password;
                $shareable_password = ($request['password'] != '')?CustomEncryptor::decrypt($request['password'], env('SALT')):$record->shareable_password;
            }else{
                $password='';
            }

            if($request['type'] == 'file'){


                    if($request->hasFile('m3u_file')){

                        $file = $request->file('m3u_file');
                        $filename = rand() . '.m3u';
                        $file->move('m3u_files',$filename);
                        if($record->stream_line != '' && file_exists('m3u_files/' . $record->stream_line)){
                            unlink('m3u_files/' . $record->stream_line);
                        }
                    }
                  
                    $postData = [

                                'mac_id' => $mac_id,
                                'playlist_name' => $request['playlist_name'],
                                'stream_line' => $filename ?? $record->stream_line,
                                'filepath' => request()->root() . '/' . 'm3u_files/' . ($filename ?? $record->stream_line),
                                'epg' => $request['xmltv_url'],
                                'type' => 'file',
                                'epg_countries' => $request['epg_countries'],
                                'logos' => $request['logos'],
                                'save_online' => ($request['save_online'] == 1)?1:0,
                                'detect_epg' => ($request['detect_epg'] == 1)?1:0,
                                'disable_groups' => ($request['disable_groups'] == 1)?1:0,
                                'is_protected' => ($request['is_protected'] == 1)?1:0,
                                'password' => $password,
                                'shareable_password' => $shareable_password,
                                'status' => ($request['status'] == 'active')?1:0,
                                'updated_at' => date('Y-m-d H:i:s')
                            ];
                            Playlist::where('id',$request['id'])->update($postData);
                            manage_logs('Playlist update',$postData,'Playlist updated successfully.',Auth::user()->id);
                            
                
            }else if($request['type'] == 'url'){

                    $postData = [
                                
                                'mac_id' => $mac_id,
                                'playlist_name' => $request['playlist_name'],
                                'stream_line' => $request['m3u_url'],
                                'epg' => $request['xmltv_url'],
                                'type' => 'url',
                                'epg_countries' => $request['epg_countries'],
                                'logos' => $request['logos'],
                                'save_online' => ($request['save_online'] == 1)?1:0,
                                'detect_epg' => ($request['detect_epg'] == 1)?1:0,
                                'disable_groups' => ($request['disable_groups'] == 1)?1:0,
                                'is_protected' => ($request['is_protected'] == 1)?1:0,
                                'password' => $password,
                                'shareable_password' => $shareable_password,
                                'status' => ($request['status'] == 'active')?1:0,
                                'updated_at' => date('Y-m-d H:i:s')
                            ];
                            Playlist::where('id',$request['id'])->update($postData);
                            manage_logs('Playlist update',$postData,'Playlist updated successfully.',Auth::user()->id);
                
            }
            return redirect()->route('client-playlist-list')->with('success','Playlist updated successfully.');
            //Updation work ends here
        }
     
       
        $device = $this->get_device_details();
        $record = Playlist::where('id',$id)->first();
        $record =  [
            'id' => $record->id,
            'playlist_name' => $record->playlist_name,
            'stream_line' => $record->stream_line,
            'epg' => $record->epg,
            'type' => $record->type,
            'epg_countries' => $record->epg_countries,
            'logos' => $record->logos,
            'save_online' => $record->save_online,
            'detect_epg' => $record->detect_epg,
            'disable_groups' => $record->disable_groups,
            'is_protected' => $record->is_protected,
            'password_exists' => ($record->password != '')?1:0,
            'status' => $record->status,
            'created_at' => Carbon::parse($record->created_at)->format('jS F Y'),

        ];
       
        return Inertia::render('PlaylistEdit',['record'=>$record, 'devicedetails'=> $device]);
    }

    public function download_file($filename){
        $path = public_path('m3u_files/' . $filename);
        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }

        return response()->download($path, $filename, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }


    public function delete_playlist(Request $request){
        $record=Playlist::where('id',$request['id'])->first();
        if($record->stream_line != '' && $record->type=='file'){
            $filePath = 'm3u_files/' . $record->stream_line;
            if(file_exists($filePath)){
                unlink($filePath);
            }
        }
       
        $record->delete();
        manage_logs('Playlist delete',$request['id'],'Playlist deleted successfully',Auth::user()->id);
        return redirect()->route('client-playlist-list')->with('success','Playlist deleted successfully.');
    }

    public function change_password(Request $request){
        $validator = \Validator::make($request->all(),[
            'password' => 'required|confirmed',
            'password_confirmation' => 'required'
        ]);

        if($validator->fails()){
             return response()->json([
                'errors' => true,
                'status' => 'failure',
                'message' =>  $validator->errors()
            ]);
        }else{

            \DB::table('personal_access_tokens')
            ->where('tokenable_id', Auth::id())
            ->where('tokenable_type', get_class(Auth::user()))
            ->delete();

            $salt = env('SALT');
            User::where('id',Auth::user()->id)->update([
                'password' => Hash::make($request['password']),
                'shareable_password' => CustomEncryptor::encrypt($request['password'], $salt),
            ]);
            $request->session()->put('device_key', $request['password']);
            return response()->json([
                'errors' => false,
                'status' => 'success',
                'message' =>  'Password changed successfully.'
            ]);

        }
    }

    public function validate_password(Request $request){
       $record =  Playlist::where('id',$request['id'])->first();
       if(!Hash::check($request['password'],$record->password)){
            return response()->json([
                'errors' => true,
                'status' => 'failure',
                'message' =>  'Invalid Password.'
            ]);
       }else{

            return response()->json([
                'errors' => false,
                'status' => 'success',
                'message' =>  'Authenticated.'
            ]);
       }
    }

    protected function get_device_details(){
        $salt = env('SALT');
        $device = MacDevice::where('mac_address', Auth::user()->username)->first();
        $device_key =  CustomEncryptor::decrypt(Auth::user()->shareable_password, $salt);
        $device->device_key = $device_key;
        return $device;
    }


    public function logout(Request $request){
         Auth::logout();
         $request->session()->forget('device');
         $request->session()->forget('device_key');
         return redirect('/');
    }


}
