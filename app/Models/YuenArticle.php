<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YuenArticle extends Model
{
    protected $table = "UserPost";
    protected $primaryKey = "WID";
    protected $keyType = "integer";
    public $timestamps = false;

    protected $fillable = [
        'UID',
        'InProgress',
        'Title',
        'Article',
        'ItemLink',
        'ItemIMG',
        'PostTime',
        'ChangeTime',
        'ConcessionStart',
        'ConcessionEnd',
        'ReportTimes',
        'Hiding',
        'location_tag',
        'product_tag',
    ];
}