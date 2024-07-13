<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = "article";
    protected $primaryKey = "id";
    protected $keyType = "integer";
    public $timestamps = false;

    protected $fillable = [
        'discount',
        'article_title',
        'article_content',
        'image',
        'likes',
        'unlikes',
    ];
}
