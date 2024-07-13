<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostMessage;
use Illuminate\Support\Facades\Auth;
use App\Models\UserPost;
use Carbon\Carbon;


class PostMessageController extends Controller
{
    public function store(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['error' => '請登入會員'], 401);
            }

            $articleExists = UserPost::where('WID', $request->WID)->exists();
            if (!$articleExists) {
                return response()->json(['error' => '該文章不存在'], 404);
            }

            $user = Auth::user();

            // 檢查使用者在10秒內是否已經發過評論
            $recentPostTime = PostMessage::where('UID', $user->id)
                ->where('WID', $request->WID)
                ->orderBy('MSGPostTime', 'desc')
                ->value('MSGPostTime');
            if ($recentPostTime && Carbon::parse($recentPostTime)->addSeconds(10)->greaterThan(Carbon::now())) {
                return response()->json(['error' => '您的嘗試過於頻繁。請稍後再試'], 429);
            }

            $validatedData = $request->validate([
                'WID' => 'required|exists:UserPost,WID',
                'MSGPost' => 'required|string',
                'user_id' => 'exists:users,id',
            ], [
                'WID.required' => '請提供文章ID。',
                'WID.exists' => '該文章不存在。',
                'MSGPost.required' => '請填寫評論內容。',
                'user_id.exists' => '該用戶不存在。',
            ]);

            $postMessage = new PostMessage;
            $postMessage->WID = $validatedData['WID'];
            $postMessage->UID = $user->UID;
            $postMessage->MSGPost = $validatedData['MSGPost'];
            $postMessage->user()->associate($user);
            $postMessage->save();

            return response()->json([
                'message' => '評論建立成功',
                'postmessage' => $postMessage
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // 確保錯誤訊息格式與前端預期一致
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // 捕獲其他異常,並返回一般的錯誤訊息
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function index(Request $request)
    {
        $articleExists = UserPost::where('WID', $request->WID)->exists();
        if (!$articleExists) {
            return response()->json(['error' => '該文章不存在'], 404);
        }

        $request->validate([
            'WID' => 'required|exists:UserPost,WID',
        ]);

        $postMessages = PostMessage::where('WID', $request->WID)
            ->with('user:id,name')
            ->get();

        // 計算評論總數
        $totalMessages = $postMessages->count();

        $postMessages = $postMessages->map(function ($postMessage) {
            return [
                'MSGWID' => $postMessage->MSGWID, // 使用評論的MSGWID欄位
                'MSGPost' => $postMessage->MSGPost,
                'MSGPostTime' => $postMessage->MSGPostTime,
                'user_name' => $postMessage->user->name,
            ];
        });

        return response()->json([
            'total_messages' => $totalMessages,
            'postMessages' => $postMessages
        ]);
    }


    public function edit(Request $request, $id)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['error' => '請登入會員'], 401);
            }

            $postMessage = PostMessage::find($id);
            if (!$postMessage) {
                return response()->json(['error' => '該評論不存在'], 404);
            }

            // 檢查使用者是否為評論的作者
            if ($postMessage->UID !== Auth::user()->id) {
                return response()->json(['error' => '您無權編輯此評論'], 403);
            }

            // 自訂驗證錯誤訊息
            $messages = [
                'MSGPost.required' => '請填寫評論內容。',
                'MSGPost.string' => '評論內容必須為字串。',
            ];

            $validatedData = $request->validate([
                'MSGPost' => 'required|string',
            ], $messages);

            // 更新評論
            $postMessage->MSGPost = $validatedData['MSGPost'];
            $postMessage->save();

            return response()->json([
                'message' => '評論更新成功',
                'postmessage' => $postMessage,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // 確保錯誤訊息格式與前端預期一致
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // 捕獲其他異常,並返回一般的錯誤訊息
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function destroy($id)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['error' => '請登入會員'], 401);
            }

            $postMessage = PostMessage::findOrFail($id);

            // 檢查使用者是否為評論的作者
            if ($postMessage->UID !== Auth::user()->id) {
                return response()->json(['error' => '您無權刪除此評論'], 403);
            }

            $postMessage->delete();

            return response()->json([
                'message' => '評論已成功刪除',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // 如果找不到評論記錄,返回 404 錯誤
            return response()->json(['error' => '該評論不存在'], 404);
        } catch (\Exception $e) {
            // 捕獲其他異常,並返回一般的錯誤訊息
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
