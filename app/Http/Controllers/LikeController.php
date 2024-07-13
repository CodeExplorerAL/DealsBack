<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LikeController extends Controller
{
    public function like(Request $request)
    {
        // 使用者對哪篇文章按過讚或倒讚
        $token = $request->token;
        $decoded_token = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))));
        $id = $decoded_token->id;

        $likes = DB::table('LikeAndDislike')
            ->where('UID', $id)
            ->select('WID', 'GiveLike', 'GiveDislike')
            ->get();

        return response()->json($likes);
    }

    public function toggleLikeDislike(Request $request)
    {
        $token = $request->token;
        $decoded_token = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))));
        $uid = $decoded_token->id;
        $wid = $request->input('wid');
        $action = $request->input('action');

        // 輸入like
        if ($action === 'like') {
            $existingRecord = DB::table('LikeAndDislike')->where('GiveLike', 1)->where('WID', $wid)->where('UID', $uid);
            $existingRecord3 = DB::table('LikeAndDislike')->where('GiveLike', 0)->where('WID', $wid)->where('UID', $uid);
            // 資料表LikeAndDislike有按讚紀錄(表示已按讚)
            if ($existingRecord->exists()) {
                // 將那筆資料更新成0
                $existingRecord->update(['GiveLike' => 0, 'GiveDislike' => 0]);
                // dd(1);
            } else if ($existingRecord3->exists()) {
                $existingRecord3->update(['GiveLike' => 1, 'GiveDislike' => 0]);
            } else {
                //若沒有則紀錄資料
                DB::table('LikeAndDislike')->updateOrInsert([
                    'WID' => $wid,
                    'UID' => $uid,
                    'GiveLike' => 1,
                    'GiveDislike' => 0,
                ]);
            }
        } else {
            $existingRecord2 = DB::table('LikeAndDislike')->where('GiveDislike', 1)->where('WID', $wid)->where('UID', $uid);
            $existingRecord4 = DB::table('LikeAndDislike')->where('GiveDislike', 0)->where('WID', $wid)->where('UID', $uid);
            if ($existingRecord2->exists()) {
                $existingRecord2->update(['GiveLike' => 0, 'GiveDislike' => 0]);
            } elseif ($existingRecord4->exists()) {
                $existingRecord4->update(['GiveLike' => 0, 'GiveDislike' => 1]);
            } else {
                DB::table('LikeAndDislike')->updateOrInsert([
                    'WID' => $wid,
                    'UID' => $uid,
                    'GiveLike' => 0,
                    'GiveDislike' => 1,
                ]);
            }
        }
        return response()->json(['success' => true]);
    }
}
