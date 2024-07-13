<?php

use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;




Route::get('/', function () {
    return view('welcome');
});
Route::get('/member', function () {
    return view('member');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::get('reset', [PasswordResetLinkController::class, "create"]);
Route::post('password/email', [PasswordResetLinkController::class, "store"]);

require __DIR__ . '/auth.php';

// Auth::routes(); //刪除才能註冊

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
