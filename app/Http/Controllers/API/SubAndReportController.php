<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserPost;
use App\Models\PostMessage;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\SubAndReport;
use Illuminate\Support\Facades\Auth;
// 在 reportArticle 和 reportComment 方法中加入以下程式碼
use App\Mail\ReportNotification;        // 用於檢舉時給管理者的模板
use App\Mail\ReportNotificationUser;    // 用於檢舉時給使用者的模板
use Illuminate\Support\Facades\Mail;


class SubAndReportController extends Controller
{
    public function subscribe(Request $request, $userId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => '請登入會員'], 401);
        }

        // 檢查目標使用者是否存在
        $targetUser = User::find($userId);
        if (!$targetUser) {
            return response()->json(['error' => '用戶不存在'], 404);
        }

        $subscription = SubAndReport::where('UID', $user->id)
            ->where('TargetUID', $userId)
            ->first();

        if ($subscription) {
            $subscription->delete();
            $message = '取消訂閱';
        } else {
            SubAndReport::create([
                'UID' => $user->id,
                'TargetUID' => $userId,
            ]);
            $message = '訂閱成功';
        }

        return response()->json([
            'message' => $message,
        ]);
    }


    public function checkSubscription($userId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => '請登入會員'], 401);
        }

        $isSubscribed = SubAndReport::where('UID', $user->id)
            ->where('TargetUID', $userId)
            ->exists();

        return response()->json([
            'isSubscribed' => $isSubscribed,
        ]);
    }


    public function storeTarget(Request $request, $articleId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => '請登入會員'], 401);
        }

        // 檢查文章是否存在
        $article = UserPost::find($articleId);
        if (!$article) {
            return response()->json(['error' => '文章不存在'], 404);
        }

        $subscription = SubAndReport::where('UID', $user->id)
            ->where('TargetWID', $articleId)
            ->first();

        if ($subscription) {
            $subscription->delete();
            $message = '取消收藏';
        } else {
            SubAndReport::create([
                'UID' => $user->id,
                'TargetWID' => $articleId,
            ]);
            $message = '收藏成功';
        }

        return response()->json([
            'message' => $message,
        ]);
    }

    public function checkFavorite($articleId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => '請登入會員'], 401);
        }

        $isFavorited = SubAndReport::where('UID', $user->id)
            ->where('TargetWID', $articleId)
            ->exists();

        return response()->json([
            'isFavorited' => $isFavorited,
        ]);
    }


    public function reportArticle(Request $request, $articleId)
{
    try {
        // 檢查文章是否存在
        $article = UserPost::where('WID', $articleId)->first();
        if (!$article) {
            return response()->json(['error' => '指定的文章不存在'], 404);
        }

        // 獲取當前認證的用戶
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => '用戶未登錄'], 401);
        }


        // 檢查使用者今天是否超過檢舉次數
        $todayReports = SubAndReport::where('UID', $user->id)
            ->whereDate('created_at', today())
            ->count();

        // 檢查使用者這小時是否超過檢舉次數
        $currentHourReports = SubAndReport::where('UID', $user->id)
            ->whereBetween('created_at', [now()->startOfHour(), now()])
            ->count();

        if ($todayReports >= 5) {
            // 限制每天最多檢舉 5 次
            return response()->json(['error' => '您今天已超過檢舉次數上限,請明天再試。'], 429);
        }

        if ($currentHourReports >= 3) {
            // 限制每小時最多檢舉 3 次
            return response()->json(['error' => '您這個小時已超過檢舉次數上限,請稍後再試。'], 429);
        }


        // 自定義錯誤訊息
        $messages = [
            'ReportContent.required' => '請填寫檢舉內容。',
            'ReportContent.min' => '請至少輸入5個字以幫助我們更了解原因。',
        ];

        // 驗證請求參數
        $validatedData = $request->validate([
            'ReportContent' => 'required|string|min:5',
        ], $messages);


        // 創建新的 SubAndReport 實例
        $subAndReport = new SubAndReport;
        $subAndReport->UID = $user->id;
        $subAndReport->ReportWID = $articleId;
        $subAndReport->ReportContent = $validatedData['ReportContent'];
        $subAndReport->status = 'pending';
        $subAndReport->save();

        // 發送電子郵件通知給管理員
        $adminEmail = 'allison917917@gmail.com';
        Mail::to($adminEmail)->send(new ReportNotification($subAndReport));

        // 發送電子郵件通知給使用者
        Mail::to($user->email)->send(new ReportNotificationUser($subAndReport));

        // 返回成功響應
        return response()->json([
            'message' => '已成功發送文章檢舉信',
            'subAndReport' => $subAndReport,
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        // 確保錯誤訊息格式與前端預期一致
        return response()->json(['errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        // 捕獲其他異常,並返回一般的錯誤訊息
        return response()->json(['error' => $e->getMessage()], 400);
    }
}




public function reportComment(Request $request, $commentID)
{
    try {
        // 檢查評論是否存在
        $comment = PostMessage::where('MSGWID', $commentID)->first();
        if (!$comment) {
            return response()->json(['error' => '指定的評論不存在'], 404);
        }

        // 獲取當前認證的用戶
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => '用戶未登錄'], 401);
        }

        // 檢查使用者今天是否超過檢舉次數
        $todayReports = SubAndReport::where('UID', $user->id)
            ->whereDate('created_at', today())
            ->count();

        // 檢查使用者這小時是否超過檢舉次數
        $currentHourReports = SubAndReport::where('UID', $user->id)
            ->whereBetween('created_at', [now()->startOfHour(), now()])
            ->count();

        if ($todayReports >= 5) {
            // 限制每天最多檢舉 5 次
            return response()->json(['error' => '您今天已超過檢舉次數上限,請明天再試。'], 429);
        }

        if ($currentHourReports >= 3) {
            // 限制每小時最多檢舉 3 次
            return response()->json(['error' => '您這個小時已超過檢舉次數上限,請稍後再試。'], 429);
        }


        // 自定義錯誤訊息
        $messages = [
            'ReportContent.required' => '請填寫檢舉內容。',
            'ReportContent.min' => '請至少輸入5個字以幫助我們更了解原因。',
        ];

        // 驗證請求參數
        $validatedData = $request->validate([
            'ReportContent' => 'required|string|min:5',
        ], $messages);


        // 創建新的 SubAndReport 實例
        $subAndReport = new SubAndReport;
        $subAndReport->UID = $user->id;
        $subAndReport->ReportMSGWID = $commentID;
        $subAndReport->ReportContent = $validatedData['ReportContent'];
        $subAndReport->status = 'pending';
        $subAndReport->save();

        // 發送電子郵件通知給管理員
        $adminEmail = 'allison917917@gmail.com';
        Mail::to($adminEmail)->send(new ReportNotification($subAndReport));

        // 發送電子郵件通知給使用者
        Mail::to($user->email)->send(new ReportNotificationUser($subAndReport));

        // 返回成功響應
        return response()->json([
            'message' => '已成功發送評論檢舉信',
            'subAndReport' => $subAndReport
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        // 確保錯誤訊息格式與前端預期一致
        return response()->json(['errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        // 捕獲其他異常,並返回一般的錯誤訊息
        return response()->json(['error' => $e->getMessage()], 400);
    }
}

}
