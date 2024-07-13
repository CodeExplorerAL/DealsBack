<?php

use App\Http\Controllers\AboutController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UpdateController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\CaptchaServiceController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\RankController;

// 李安

use App\Http\Controllers\API\UserPostController;
use App\Http\Controllers\API\PostMessageController;
use App\Http\Controllers\API\SubAndReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


// 註冊系統
Route::middleware('guest')->group(function () {
    Route::post('register', [RegisteredUserController::class, 'store']);
    // ->name('register');
});
// 信箱認證 
Route::post("email", [RegisteredUserController::class, "email"]);


// 登入系統
Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    // Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});

// 修改系統
Route::post("update", [AboutController::class, "update_item"]);

// 搜尋系統
Route::post("search", [SearchController::class, "search"]);

// 忘記密碼發送信件
Route::post('forgot-password', [PasswordResetLinkController::class, 'store']);

// 信件裡的重設密碼
Route::post("resetpassword", [UpdateController::class, "update_password"]);

// 關於我
Route::post("aboutme", [AboutController::class, "about"]);
Route::post("post", [AboutController::class, "post"]);
// 自我介紹
Route::post("PersonaIProfile", [AboutController::class, "collet"]);

// 驗證碼圖片系統
Route::get('/reload-captcha', [CaptchaServiceController::class, 'reloadCaptcha']);

// 排行榜
Route::get('/rank', [RankController::class, 'index']);

Route::middleware('auth:api')->post('/like', [LikeController::class, 'like']);
// Route::post('/Wlike', [LikeController::class, 'Wlike']);
Route::middleware('auth:api')->post('/a', [LikeController::class, 'toggleLikeDislike']);

Route::controller(UserPostController::class)->group(function () {
    // 發文
    Route::post('articles/post', 'store');
    //編輯文章
    Route::post('articles/update', 'UpdatePost');
    Route::get('articles', 'index');
    //刪除文章
    Route::get('delete', 'destory');
    // 顯示單頁文章
    Route::get('articles/{id}', 'show')
        ->where('id', '[0-9]+');
    // 標籤導向
    Route::get('articles/search', 'search');
});


Route::controller(PostMessageController::class)->group(function () {
    // 發表評論
    Route::post('PostMessage/tosql', 'store');
    // 顯示評論
    Route::get('PostMessage', 'index');
});


Route::controller(SubAndReportController::class)->group(function () {
    // 訂閱使用者
    Route::post('/users/{user}/subscribe', 'subscribe');
    // 獲取訂閱使用者
    Route::get('/users/{user}/checkSubscription', 'checkSubscription');
    // 點擊收藏
    Route::post('/articles/{article}/storeTarget', 'storeTarget');
    // 獲取收藏
    Route::get('/articles/{article}/checkFavorite', 'checkFavorite');
    // 檢舉文章
    Route::post('/articles/{article}/report', 'reportArticle');
    // 檢舉評論
    Route::post('/comments/{comment}/report', 'reportComment');
});
