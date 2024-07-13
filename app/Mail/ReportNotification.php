<?php

namespace App\Mail; // 指定這個類別所在的命名空間

use App\Models\SubAndReport; // 引入了 SubAndReport 模型
use Illuminate\Bus\Queueable; // 支持將郵件放入隊列
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels; // 序列化模型數據以便在隊列中處理

class ReportNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $subAndReport;

    // 接收一個 SubAndReport 實例作為參數，並將其存儲在 $subAndReport 屬性中
    public function __construct(SubAndReport $subAndReport)
    {
        $this->subAndReport = $subAndReport;
    }

    

    // 郵件構建方法 (Build Method)
    public function build()
    {
        return $this->subject('[請處理]Deals網站收到檢舉通知') // 設置郵件主題
                    ->view('report-notification') // 指定使用名為 report-notification.blade.php 的視圖來渲染郵件內容
                    ->with([ // 傳遞資料到郵件視圖，這裡傳遞了 ReportWID 和 ReportMSGWID 和 MSGPost 和 reportContent 資料。
                        'ReportWID' => $this->subAndReport->ReportWID,
                        'Title' => $this->subAndReport->Title,
                        'Article' => $this->subAndReport->Article,
                        'ReportMSGWID' => $this->subAndReport->ReportMSGWID,
                        'MSGPost' => $this->subAndReport->MSGPost,
                        'reportContent' => $this->subAndReport->ReportContent,
                    ]);
    }
}