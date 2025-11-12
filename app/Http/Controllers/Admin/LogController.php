<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Log;



class LogController extends Controller
{
    public function index(){

        $status = 1;
        $record = \DB::table('tbl_logs_status')->first();
        if($record){
            $status = $record->status;
        }

        $records = Log::orderBy('id','desc')->get()->map(function ($record) {
            return [
                    'id' => $record->id,
                    'action' =>  $record->action,
                    'request_data' => $record->request_data,
                    'response_data' => $record->response_data,
                    'by' => ($record->user->is_admin == 0)?$record->user->email:$record->user->username,
                    'role' => $record->user->is_admin,
                    'created_at' => Carbon::parse($record->created_at)->format('jS F Y'),
                ];
        });

        return Inertia::render('Admin/LogList',['records'=> $records,'status'=> $status]);
    }



    public function manage_status(Request $request){

        $message = '';
        $status = ($request['status'] == 1)?0:1;
        $message = ($request['status'] == 1)?'disabled':'enabled';
        $record=\DB::table('tbl_logs_status')->first();
        if($record){
            \DB::table('tbl_logs_status')->update([
                    'status' => $status,
                    'updated_at'=> date('Y-m-d H:i:s')
            ]);
        }else{
           \DB::table('tbl_logs_status')->insert([
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
           ]);
        } 
        return redirect()->route('admin-log-list')->with([
                'success' => 'Log ' . $message .  ' successfully.'
        ]);
    }


    public function delete_log(Request $request){
        $record=Log::where('id',$request['id'])->delete();
        return redirect()->route('admin-log-list')->with('success','Log deleted successfully');
    }
}
