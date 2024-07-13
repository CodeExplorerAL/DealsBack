<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Illuminate\View\View;


class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request)
    {
        DB::insert("insert into users (name, email, password) values (?, ?, ?)", [$request->name, $request->email,  Hash::make($request->password)]);
        echo "OK";
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    // 註冊系統 
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required'],
        ]);
        $encrypter = app(\Illuminate\Contracts\Encryption\Encrypter::class);
        $decryptedString = $encrypter->decrypt($request->captcha, false);
        $extracted_part = explode('|', $decryptedString)[1];
        // 輸入格答案
        $RV = $request->rv;

        $existingUserEmail = User::where('email', $request->email)->first();
        // return $existingUserEmail;
        // return $existingUserEmail;
        if ($extracted_part === $RV) {
            // 如果DB存在這筆信箱，去判斷有沒有通過驗證
            if ($existingUserEmail) {
                // 沒通過驗證，發信給他
                // 有通過驗證，直接回傳'你已經成功註冊，並且通過驗證'
                $hasVerified = User::select("email_verified_at")->where('email', "=", $request->email)->first()->email_verified_at;
                // return $hasVerified;
                if ($hasVerified === null) {
                    $existingUserEmail->sendEmailVerificationNotification();
                    return response()->json(['message' => '你已經註冊過此信箱，尚未驗證 -----> 請到信箱點擊驗證']);
                } else {
                    return response()->json([
                        'message' => '你已經成功註冊，並且通過驗證',
                    ]);
                }
            }
            // 如果DB找不到這筆信箱，插入使用者，然後寄驗證信
            else {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);
                if ($user) {
                    $user->sendEmailVerificationNotification();
                    return response()->json([
                        "message" => "成功發送郵件驗證通知",
                    ]);
                } else {
                    return response()->json([
                        "message" => "發送郵件失敗",
                    ]);
                }
            }
            return response()->json([
                "message" => "驗證成功"
            ]);
        } else {
            return response()->json([
                "message" => "驗證失敗"
            ]);
        }
    }


    public function email(Request $request)
    {
        // return $request;
        $email = $request->email;

        $updateData['email_verified_at'] = Carbon::now();
        User::where('email', '=', $email)->update($updateData);
    }
}
