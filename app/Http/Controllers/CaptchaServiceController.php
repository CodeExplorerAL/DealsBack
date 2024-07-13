<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CaptchaServiceController extends Controller
{
    //index() – 它使用驗證碼元素在視圖中載入表單範本。
    public function index()
    {
        return view('index');
    }
    //capthcaFormValidate() – 驗證整個表單，包括驗證碼輸入欄位。
    public function capthcaFormValidate(Request $request)
    {
        $encrypter = app(\Illuminate\Contracts\Encryption\Encrypter::class);

        // decrypt
        $decryptedString = $encrypter->decrypt($request->captcha, false);

        $extracted_part = explode('|', $decryptedString)[1];

        if ($extracted_part == $decryptedString) {
        }


        return response()->json([
            "captcha" => $decryptedString,

        ]);
    }
    public function reloadCaptcha()
    {

        return response()->json([
            'captcha' => captcha_img(),

        ]);
    }
}
