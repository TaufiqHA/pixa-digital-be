<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $guarded = ["id"];

    public function torrents()
    {
        return $this->hasOne(Torrent::class);
    }
}
