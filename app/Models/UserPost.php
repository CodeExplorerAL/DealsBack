<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPost extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    // 填寫資料進資料庫
    protected $table = 'UserPost';
    protected $primaryKey = 'WID';

    protected $fillable = [
        'Title', 'Article', 'ItemLink', 'ItemIMG', 'PostTime', 'ChangeTime', 'ConcessionStart', 'ConcessionEnd', 'ReportTimes', 'product_tag', 'location_tag', 'UID', 'InProgress',
    ];
    // 覆寫 updating 方法


    public $timestamps = false; // 這將禁用 updated_at 的自動更新

    /**
     * 獲取擁有該文章的使用者。
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'UID', 'id'); // 修改外鍵欄位名稱
    }
}
