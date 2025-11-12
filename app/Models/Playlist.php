<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    protected $table = 'tbl_playlists';

    public function mac_devices()
    {
        return $this->belongsTo(MacDevice::class,'mac_id');
    }
}
