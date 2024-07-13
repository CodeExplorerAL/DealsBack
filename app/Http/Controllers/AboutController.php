<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AboutController extends Controller
{


    public function about(Request $request)
    {


        $token = $request->token;
        $decoded_token = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))));
        $id = $decoded_token->id;


        $mypost = DB::table("users")
            ->join("UserPost", "users.id", "=", "UserPost.UID")
            ->join("LikeAndDislike", "UserPost.WID", "=", "LikeAndDislike.WID")
            ->where("users.id", $id)
            ->select(
                "users.name",
                "users.email",
                "users.password",
                "UserPost.WID",
                "UserPost.UID",
                "UserPost.InProgress",
                "UserPost.Title",
                "UserPost.Article",
                "UserPost.ItemLink",
                "UserPost.ItemIMG",
                "UserPost.PostTime",
                "UserPost.ChangeTime",
                "UserPost.ConcessionStart",
                "UserPost.ConcessionEnd",
                "UserPost.ReportTimes",
                "UserPost.Hiding",
                "UserPost.location_tag",
                "UserPost.product_tag",
                "LikeAndDislike.GiveLike",
                "LikeAndDislike.GiveDisLike",
            )
            ->get();


        $good = DB::table("users")
            ->join("UserPost", "users.id", "=", "UserPost.UID")
            ->join("LikeAndDislike", "UserPost.WID", "=", "LikeAndDislike.WID")
            ->where("users.id", $id)
            ->selectRaw('SUM(GiveLike) as Sumlike')
            ->groupBy('users.id')
            ->get();


        $collect = DB::table("users")
            ->join("SubAndReport", "users.id", "=", "SubAndReport.UID")
            ->join("UserPost", "SubAndReport.TargetWID", "=", "UserPost.WID")
            ->join("LikeAndDislike", "UserPost.WID", "=", "LikeAndDislike.WID")
            ->join("PostMessage", "UserPost.WID", "=", "PostMessage.WID")
            ->select(
                "UserPost.WID",
                "UserPost.UID",
                "UserPost.InProgress",
                "UserPost.Title",
                "UserPost.Article",
                "UserPost.ItemLink",
                "UserPost.ItemIMG",
                "UserPost.PostTime",
                "UserPost.ChangeTime",
                "UserPost.ConcessionStart",
                "UserPost.ConcessionEnd",
                "UserPost.ReportTimes",
                "UserPost.Hiding",
                "UserPost.location_tag",
                "UserPost.product_tag",
                "LikeAndDislike.GiveLike",
                "LikeAndDislike.GiveDisLike",
                "users.name",
                "PostMessage.UID",
                "PostMessage.MSGPost",
                "PostMessage.MSGPostTime"
            )
            ->get();

        $Self_introduction = DB::table("users")->where("users.id", $id)->select("PersonalProfile")->get();

        return response()->json([
            "讚數" => $good,
            "收藏文章" => $collect,
            "自我介紹" => $Self_introduction,
            'mypost' => $mypost
        ]);
    }

    public function post(Request $request)
    {

        $token = $request->token;
        $decoded_token = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))));
        $id = $decoded_token->id;

        $postmsg = DB::table("users")
            ->join("SubAndReport", "users.id", "=", "SubAndReport.UID")
            ->join("UserPost", "SubAndReport.TargetWID", "=", "UserPost.WID")
            ->join("PostMessage", "UserPost.WID", "=", "PostMessage.WID")
            ->where("users.id", $id)
            ->select(
                "PostMessage.UID",
                "PostMessage.MSGPost",
                "PostMessage.WID",
                "PostMessage.MSGPostTime"
            )
            ->get();

        $mypostmsg = DB::table("users")
            ->join("UserPost", "users.id", "=", "UserPost.UID")
            ->join("PostMessage", "UserPost.WID", "=", "PostMessage.WID")
            ->where("users.id", $id)
            ->select(
                "PostMessage.UID",
                "PostMessage.MSGPost",
                "PostMessage.WID",
                "PostMessage.MSGPostTime"
            )
            ->get();

        return response()->json([
            "postmsg" => $postmsg,
            "mypostmsg" => $mypostmsg,
        ]);
    }


    public function update_item(Request $request)
    {


        // COOKIE版本
        $token = $request->token;
        $decoded_token = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))));
        $email = $decoded_token->email;

        $original_image = DB::table("users")->select('image')->where('email', '=', $email)->get();
        $original_name = DB::table("users")->select('name')->where('email', '=', $email)->get();
        $original_password = DB::table("users")->select('password')->where('email', '=', $email)->get();
        $PersonalProfile = DB::table("users")->select('PersonalProfile')->where("email", "=", $email)->get();
        $updateData = [];

        // 判斷使用者修改甚麼欄位
        if (($request->imageApple) != "" && ($request->imageApple != $original_image)) {
            $updateData['image'] = $request->imageApple;
            $src = $request->imageApple;
        } else if (($request->imageApple) == "") {
            $src = $original_image;
        }

        if (($request->name) != "" && ($request->name != $original_name)) {
            $updateData['name'] = $request->name;
        }

        if (($request->PersonalProfile) != "" && ($request->PersonalProfile != $PersonalProfile)) {
            $updateData['PersonalProfile'] = $request->PersonalProfile;
        }

        if (($request->password) != "" && ($request->password != $original_password)) {
            $updateData['password'] = Hash::make($request->password);
        }

        DB::table("users")->where('email', '=', $email)->update($updateData);


        return response()->json([
            "src" => $src,
            'message' => 'Item updated successfully',
        ]);
    }
}
