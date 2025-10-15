<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Torrent extends Model
{
    protected $guarded = ['id'];

    public function torrent(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'content_id', 'id');
    }
}
