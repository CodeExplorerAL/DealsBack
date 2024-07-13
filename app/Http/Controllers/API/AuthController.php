<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\JWTAuth;



class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }



    public function login(Request $request)
    {
        // 驗證請求參數
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // 使用者輸入的答案
        $verification = $request->rv;

        // 取得使用者ID
        $id = DB::table("users")->select("id")->where("email", "=", $request->email)->get();
        $id = $id[0]->id;

        // 認證
        $credentials = $request->only('email', 'password');
        $token = auth()->claims(['email' => $request->email, 'id' => $id])
            ->setTTL(120)
            ->attempt($credentials);

        // 取得用戶資訊
        $user = Auth::user();

        // 驗證碼判斷成功而且token有值，才能成功登入
        if ($token) {
            return response()->json([
                "message" => "驗證登入成功",
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);
        } else {
            return response()->json([
                "message" => "驗證登入失敗",
            ], 401);
        }
    }


    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->token;
        echo $token;
        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
}
