<?php
namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use App\Models\MacDevice;
use App\Models\Playlist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Helpers\CustomEncryptor;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


class ApiClientController extends Controller
{
    public function create_device(Request $request){

        $validator = \Validator::make($request->all(),[
            'api_key' => 'required',
            'api_secret' => 'required',
            'secret_key' => 'required',
            'mac_address' => 'required|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/',
            'device_info' => 'required|array',
            'device_info.name' => 'required|string'
        ],[
            'api_key.required' => 'Api key is required.',
            'api_secret.required' => 'Api secret is required.',
            'secret_key.required' => 'Secret key is required.',
            'mac_address.required' => 'Mac address is required.',
            'device_info.required' => 'Device info is required.',
            'device_info.array' => 'Device info should be in array format.',
            'device_info.name' => 'Device name field is required.',
        ]);

        if($validator->fails()){
            return response()->json([
                'errors' => true,
                'status' => 'failure',
                'message' =>  $validator->errors()
            ]);
        }else{

            $salt = env('SALT');
            $apiKey = env('API_KEY');
            $apiSecret = env('API_SECRET');
            $secretKey = md5($apiKey . "*" . $salt . "*" . $apiSecret);
           
            if($request['api_key'] == $apiKey && $request['api_secret'] == $apiSecret){

                if ($secretKey != $request["secret_key"]) {
                        return response()->json([
                            'errors' => true,
                            'status' => 'failure',
                            'message' => 'Unauthorized'
                        ],401);
                        
                }else{

                    if(MacDevice::where('mac_address',$request['mac_address'])->count() > 0){

                        if(User::where('username',$request['mac_address'])->count() > 0){
                            $user = User::where('username',$request['mac_address'])->first();
                            $password = $user->shareable_password;
                            return response()->json([
                                    'errors' => false,
                                    'status' => 'success',
                                    'message' =>  'Device is already registered.',
                                    'data' => [
                                        'username'=> $request['mac_address'],
                                        'password'=> CustomEncryptor::decryptInt($password, $salt)
                                    ]
                            ]);
                        }

                    }else{

                        MacDevice::insert([
                            'device_id' =>   rand(10000, 99999),
                            'mac_address' => $request['mac_address'],
                            'device_info' => json_encode($request['device_info']),
                            'subscription_id' => $request['subscription_id'] ?? '',
                            'status' => 1,
                            'created_at' => date('Y:m:d H:i:s'),
                            'updated_at' => date('Y:m:d H:i:s')
                        ]);

                        $password = rand(10000, 99999);
                        User::create([
                            'username' => $request['mac_address'],
                            'password' => Hash::make($password),
                            'shareable_password' => CustomEncryptor::encryptInt($password, $salt),
                            'created_at' => date('Y:m:d H:i:s'),
                            'updated_at' => date('Y:m:d H:i:s')
                        ]);

                        return response()->json([
                            'errors' => false,
                            'status' => 'success',
                            'message' =>  'Device created successfully.',
                            'data' => [
                                'username' => $request['mac_address'],
                                'password' => $password
                            ]

                        ]);
                    }
                     
                }

            }else{

                return response()->json([
                    'errors' => true,
                    'status' => 'failure',
                    'message' => 'Invalid credentials.'
                ]);
            }
        }
    }


    public function login(Request $request){
      
            $validator=\Validator::make($request->all(),[
                'username'=>'required|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/',
                'password'=>'required',
            ]);

            if($validator->fails()){
                return response()->json([
                    'errors' => true,
                    'status' => 'failure',
                    'message' =>  $validator->errors()
                ]);
            }
                

            $user = User::where('username',$request['username'])->first();
            if(!$user || !Hash::check($request['password'],$user->password)){
                return response()->json([
                    'errors' => true,
                    'status' => 'failure',
                    'message' => 'Invalid Credentials'
                ],401);
            }
            $token = $user->createToken($user->username.'-AuthToken')->plainTextToken;
            return response()->json([
                'errors' => false,
                'status' => 'success',
                'access_token' => $token,
            ]);

    }

    public function get_playlist(Request $request){
            $username = $request->user()->username;
            if(MacDevice::where('mac_address',$username)->count() > 0){
                $device = MacDevice::where('mac_address',$username)->first();
                if(Playlist::where('mac_id',$device->id)->count() > 0){
                    $playlists = Playlist::where('mac_id',$device->id)->get();
                    return response()->json([
                        'errors' => false,
                        'status' => 'success',
                        'message' => 'Playlists fetched successfully',
                        'data' => $playlists
                    ]);
                }else{
                    return response()->json([
                        'errors' => false,
                        'status' => 'success',
                        'message' => 'No playlist found for this device',
                        'data' => []
                    ]);
                }
              
            }else{
                return response()->json([
                    'errors' => false,
                    'status' => 'success',
                    'message' => 'Device not found',
                ]);
            }

    }

    public function get_file_data(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => true,
                'status' => 'failure',
                'message' => $validator->errors()
            ]);
        }
        
        $url = $request['url'];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return response($response, $httpCode)->header('Content-Type', 'text/plain'); 
    }


    public function generate_qr(Request $request){

        $validator=\Validator::make($request->all(),[
            'mac_address'=>'required|regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/',
        ]);

        if($validator->fails()){
            return response()->json([
                'errors' => true,
                'status' => 'failure',
                'message' =>  $validator->errors()
            ]);
        }

        $qr_login_token = Str::random(30);
        $user = User::where('username',$request['mac_address'])->first();
        if($user){
            DB::table('qr_login_tokens')->insert([
                'user_id' => $user->id,
                'qr_login_tokens' => $qr_login_token,
                'created_at' => date('Y:m:d H:i:s'),
                'updated_at' => date('Y:m:d H:i:s')
            ]);

                $targrtUrl = 'https://smarttv.smarterspro.com/login_qr/' . $qr_login_token;
                $qrImage  = QrCode::format('png')->size(300)->generate($targrtUrl);
                $filename = "qr-codes/{$qr_login_token}.png";
                Storage::disk('public')->put($filename, $qrImage);
                $qrUrl = asset(Storage::url($filename));
                return response()->json([
                    'errors' => false,
                    'status' => 'success',
                    'qr_url' => $qrUrl,
                ]);
        }else{
             return response()->json([
                'errors' => true,
                'status' => 'failure',
                'message' => 'Account does not exists.'
            ]);
        }

       
    }


    

   
}



