<?php 
use App\Models\Log;


function manage_logs($action,$requestData,$responseData,$by){
    $record = \DB::table('tbl_logs_status')->first();
    if($record && $record->status == 1){
          Log::insert([
            'action' => $action,
            'request_data' => json_encode($requestData),
            'response_data' => $responseData,
            'user_id' => $by,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
          ]);
    }
}