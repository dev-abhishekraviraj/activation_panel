<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\Playlist;
use App\Models\MacDevice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Helpers\CustomEncryptor;



class FileUploadController extends Controller
{
    public function fileUpload(Request $request){

      
        $validator = \Validator::make($request->all(), [
           'chunk' => 'required|file|mimes:txt,m3u'
        ], [
            'chunk.required' => 'Please choose a file or enter a valid url.',
            'chunk.file'     => 'The uploaded item must be a valid file.',
            'chunk.mimes'    => 'Only .m3u or .txt files are allowed.',
        ]);

        if($validator->fails()){
            return response()->json(['errors'=>true,'message'=>$validator->errors()]);
        }

        try{
                $alluploaded=false;
                $chunk = $request->file('chunk');
                $fileName = $request->input('fileName');
                $chunkIndex = $request->input('chunkIndex');
                $totalChunks = $request->input('totalChunks');
                $tempDir =     'm3u_temp/';

                //Delete currupted files and folder  if exists
                if ($chunkIndex == 0) {
                    if (is_dir($tempDir)) {
                        $items = scandir($tempDir);
                        foreach ($items as $item) {
                            if ($item == '.' || $item == '..') {
                                continue;
                            }

                            $path = $tempDir . '/' . $item;
                            if (is_dir($path)) {
                                rmdir($path);
                            } else {
                                unlink($path);
                            }
                        }
                        rmdir($tempDir);

                    }
                }

                //Ensure temp directory exists
                if (!File::exists($tempDir)) {
                    File::makeDirectory($tempDir, 0755, true);
                }

                //Save the chunk
                $chunk->move($tempDir, $chunkIndex);
                
                //If last chunk received, assemble all
                if ((int)$chunkIndex + 1 == (int)$totalChunks) {

                    $fileName = rand() . '.m3u';
                    $finalPath = 'm3u_files/' . $fileName;
                    $output = fopen($finalPath, 'wb');
                    for ($i = 0; $i < $totalChunks; $i++) {
                        $chunkPath = $tempDir . '/' . $i;
                        $data = file_get_contents($chunkPath);
                        fwrite($output, $data);
                    }
                    fclose($output);
    
                    //Clean up chunks
                    File::deleteDirectory($tempDir);

                    //validate m3u file
                    $content = file_get_contents($finalPath);
                    if(strpos($content, '#EXTM3U') !== 0) {
                        if(file_exists(($finalPath))){
                            unlink($finalPath);
                        }
                        return response()->json(['errors'=>true,'message'=>['chunk'=>['Invalid M3U: Missing #EXTM3U header.']]]);
                    }
                    if(strpos($content, '#EXTINF:') === false) {
                        if(file_exists(($finalPath))){
                            unlink($finalPath);
                        }
                        return response()->json(['errors'=>true,'message'=>['chunk'=>['Valid header, but no media entries (#EXTINF).']]]);
                    }
                   //validate m3u file
                    $alluploaded=true;
                    if($request['id'] != ''){
                        $record = Playlist::where('id',$request['id'])->first();
                        if(file_exists('m3u_files/' . $record['stream_line'])){
                            unlink('m3u_files/' . $record['stream_line']);
                        }
                    }

                }

                if($alluploaded){
                    if(Auth::user()->is_admin == 1){
                        $mac_id = MacDevice::where('mac_address',Auth::user()->username)->first()->id;
                    }else{
                        $mac_id = $request['mac_id'];
                    }
                    $password='';
                    if($request['is_protected'] == 1){
                        if($request['id'] != ''){
                            if($request['password'] != ''){
                                $password = Hash::make($request['password']);
                                $shareable_password = CustomEncryptor::encrypt($request['password'], env('SALT'));
                            }else{
                                $record = Playlist::where('id',$request['id'])->first();
                                $password = $record['password'];
                                $shareable_password = $record['shareable_password'];
                            }
                        }else{
                                $password = Hash::make($request['password']);
                                $shareable_password = CustomEncryptor::encrypt($request['password'], env('SALT'));
                        }
                        
                    }
                    
                    
                    if($request['id'] == ''){
                        $postData = [

                            'mac_id' => $mac_id,
                            'playlist_name' => $request['playlist_name'],
                            'stream_line' => $fileName,
                            'filepath' => request()->root() . '/' . 'm3u_files/' . $fileName,
                            'epg' => $request['xmltv_url'] ?? '',
                            'type' => 'file',
                            'epg_countries' => $request['epg_country'] ?? '',
                            'logos' => $request['logo'] ?? '',
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
                        return response()->json(['errors'=>false,'message'=>'Playlist created successfully.']);
                    }else{
                        $record = Playlist::where('id',$request['id'])->first();
                        $postData = [

                            'mac_id' => $mac_id,
                            'playlist_name' => $request['playlist_name'],
                            'stream_line' => $fileName,
                            'filepath' => request()->root() . '/' . 'm3u_files/' . ($fileName),
                            'epg' => $request['xmltv_url'],
                            'type' => 'file',
                            'epg_countries' => $request['epg_country'],
                            'logos' => $request['logo'],
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
                        manage_logs('Playlist update',$postData,'Playlist updated successfully',Auth::user()->id);
                        $request->session()->flash('success','Playlist updated successfully.');
                        return response()->json(['errors'=>false,'message'=>'done']);
                    }
                }

        }
        catch(\Exception $e){
            return response()->json(['errors'=>true,'message'=>$e->getMessage()]);
        }

        
    }
    
}
