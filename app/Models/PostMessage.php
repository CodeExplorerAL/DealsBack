<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostMessage extends Model
{
    protected $table = 'PostMessage';

    protected $primaryKey = 'MSGWID';

    public $timestamps = false;

    protected $fillable = [
        'WID',
        'UID',
        'MSGPost',
        'MSGPostTime',
        'MSGChangeTime',
        'MSGReport',
        'MSGHiding',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UID', 'id');
    }

    public function userPost()
    {
        return $this->belongsTo(UserPost::class, 'WID', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($postMessage) {
            $postMessage->MSGChangeTime = now();
        });
    }
}
