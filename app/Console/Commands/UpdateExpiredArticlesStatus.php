<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserPost;
use Carbon\Carbon;

class UpdateExpiredArticlesStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:update-expired-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新文章狀態(使用優惠結束日和開始日判斷)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now();

        // 更新已過期的文章狀態為 "優惠已結束"
        UserPost::where('ConcessionEnd', '<', $now)
            ->update(['InProgress' => '已結束']);

        // 更新優惠未開始的文章狀態為 "優惠未開始"
        UserPost::where('ConcessionStart', '>', $now)
            ->update(['InProgress' => '準備中']);

        // 更新優惠中的文章狀態為 "優惠中"
        UserPost::where('ConcessionStart', '<=', $now)
            ->where('ConcessionEnd', '>=', $now)
            ->update(['InProgress' => '優惠中']);

        // 將ConcessionEnd為NULL的文章狀態更新為"永久"
        UserPost::whereNull('ConcessionEnd')
            ->update(['InProgress' => '永久']);

        $this->info('Article status updated successfully!');
    }
}
