<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MacDevice extends Model
{
    protected $table = 'tbl_mac_devices';
    protected $fillable=['device_id','mac_address','device_info','subscription_id','status','created_at','updated_at'];

    public function playlists()
    {
        return $this->hasMany(Playlist::class,'mac_id');
    }
}
