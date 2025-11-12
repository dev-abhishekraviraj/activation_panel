<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Models\Playlist;
use App\Models\MacDevice;
use Carbon\Carbon;




class PlaylistController extends Controller
{

    
    public function index(){

        $records = Playlist::orderBy('id','desc')->get()->map(function ($record) {
            return [
                'id' => $record->id,
                'stream_line' => $record->stream_line,
                'type' => $record->type,
                'is_protected' => $record->is_protected,
                'status' => $record->status,
                'created_at' => Carbon::parse($record->created_at)->format('jS F Y'),

            ];
        });
       
        return Inertia::render('Admin/PlaylistList',['records' => $records]);
    }

    public function create_playlist(Request $request){
      
        if($request->isMethod('POST')){

            if($request['type'] == 'file'){
                $m3uUrlValidation = '';
                $m3uFileValidation = 'required|file|mimes:txt,m3u,m3u8|max:2048';
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
                'mac_id' => 'required',
                'status' => 'required',
                'm3u_file' => $m3uFileValidation,
                'm3u_url' =>  $m3uUrlValidation,
                'password' => $passwordValidation
            ];

            $error_messages = [
                'mac_id.required' => 'Mac address field is required',
                'm3u_file.required' => 'Please choose a file or enter a valid url',
                'm3u_url.required' => 'Please choose a file or enter a valid url',
                'password.required' => 'Password field is required',
                'password.confirmed' => 'Password and confirm password must be same',
            ];
           
            $request->validate($rules,$error_messages);

            if($request['type'] == 'file' && $request->hasFile('m3u_file')){
                    //Check that the file contains a valid M3U header
                    $fileContents = file_get_contents($request->file('m3u_file')->getPathname());
                    if (strpos($fileContents, '#EXTM3U') === false) {
                        return redirect()->back()->withErrors(['m3u_file' => 'Please choose a valid m3u file.']);
                    }
                
            }
           
           
            if($request['type'] == 'file'){
                    $file = $request->file('m3u_file');
                    $filename = rand() . '-m3u-' . $file->getClientOriginalName();
                    $file->move('m3u_files',$filename);
                    $postData = [
                                'mac_id' => $request['mac_id'],
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
                                'password' => \Hash::make($request['password']) ?? '',
                                'status' => ($request['status'] == 'active')?1:0,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ];
                            Playlist::insert($postData);
                            manage_logs('Playlist create',$postData,'Playlist created successfully.',\Auth::user()->id);
                            
                
            }
            else if($request['type'] == 'url'){
               
                    $postData = [
                                
                                'mac_id' => $request['mac_id'],
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
                                'password' => ($request['password'] != '')?\Hash::make($request['password']):'',
                                'status' => ($request['status'] == 'active')?1:0,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ];
                            Playlist::insert($postData);
                            manage_logs('Playlist create',$postData,'Playlist created successfully.',\Auth::user()->id);
                           
                
            }
            return redirect()->route('admin-playlist-list')->with('success','Playlist created successfully.');
        }

        $devices = MacDevice::all();
        return Inertia::render('Admin/PlaylistCreate',['devices'=>$devices]);
    }


    public function edit_playlist(Request $request,$id = ''){

        if($request->isMethod('POST')){
           
            if($request['type'] == 'file'){
                $m3uUrlValidation = '';
                $m3uFileValidation = 'nullable|file|mimes:txt,m3u,m3u8|max:2048';
            }else if($request['type'] == 'url'){
                $m3uFileValidation = '';
                $m3uUrlValidation = 'required|url';
            }

            $record = Playlist::where('id',$request['id'])->first();
            if($request['is_protected'] == 1 && $record->password == ''){
                $passwordValidation = 'required|confirmed';
            }else{
                $passwordValidation = '';
            }

            $rules = [
                'playlist_name' => 'required',
                'mac_id' => 'required',
                'status' => 'required',
                'm3u_file' => $m3uFileValidation,
                'm3u_url' =>  $m3uUrlValidation,
                'password' => $passwordValidation
            ];

            $error_messages = [
                'mac_id.required' => 'Mac address field is required.',
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

            //Updation works starts
            if($request['is_protected'] == 1){
                $password=($request['password'] != '')?\Hash::make($request['password']):$record->password;
            }else{
                $password='';
            }
            if($request['type'] == 'file'){

                if($request->hasFile('m3u_file')){
                    $file = $request->file('m3u_file');
                    $filename = rand() . '-m3u-' . $file->getClientOriginalName();
                    $file->move('m3u_files',$filename);
                    if($record->stream_line != '' && file_exists('m3u_files/' . $record->stream_line)){
                        unlink('m3u_files/' . $record->stream_line);
                    }
                }
                
                $postData = [
                            'mac_id' => $request['mac_id'],
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
                            'status' => ($request['status'] == 'active')?1:0,
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        Playlist::where('id',$request['id'])->update($postData);
                        manage_logs('Playlist update',$postData,'Playlist updated successfully.',\Auth::user()->id);
                            
                
            }
            else if($request['type'] == 'url'){

                $postData = [
                    
                    'mac_id' => $request['mac_id'],
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
                    'status' => ($request['status'] == 'active')?1:0,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                Playlist::where('id',$request['id'])->update($postData);
                manage_logs('Playlist update',$postData,'Playlist updated successfully.',\Auth::user()->id);
                        
            
                
            }
           
            return redirect()->route('admin-playlist-list')->with('success','Playlist updated successfully.');
            //Updation work ends
        }
       
        $devices = MacDevice::all();
        $record = Playlist::where('id',$id)->first();
        $record =  [
            'id' => $record->id,
            'mac_id' => $record->mac_id,
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
            'status' => $record->status,
            'created_at' => Carbon::parse($record->created_at)->format('jS F Y'),

        ];
                
        return Inertia::render('Admin/PlaylistEdit',['record'=>$record,'devices'=>$devices]);
    }
   

    public function check_password(Request $request){
       $record =  Playlist::where('id',$request['id'])->first();
       if(!\Hash::check($request['password'],$record->password)){
            return response()->json([
                'errors' => true,
                'status' => 'failure',
                'message' =>  'Invalid Password'
            ]);
       }else{
            return response()->json([
                'errors' => false,
                'status' => 'success',
                'message' =>  'Password Verified'
            ]);
       }
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
        manage_logs('Playlist delete',$request['id'],'Playlist deleted successfully.',\Auth::user()->id);
        return redirect()->route('admin-playlist-list')->with('success','Playlist deleted successfully.');
    }
}
