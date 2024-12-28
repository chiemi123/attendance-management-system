<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Auth ファサードを追加
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\UserController;



/*
|---------------------------------------------------------------------------
| Web Routes
|---------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/test-mail', function () {
    Mail::raw('This is a test email.', function ($message) {
        $message->to('test@example.com')
            ->subject('Test Email');
    });
    return 'Test email sent!';
});

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/')->with('success', 'メールアドレスが認証されました'); // 認証成功後にホーム画面にリダイレクト
    })->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('/email/resend', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.resend'); // 1時間に6回の再送信制限を追加
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('index');
    Route::post('/work', [AttendanceController::class, 'work'])->name('work');
    Route::get('/attendance', [AttendanceController::class, 'attendance'])->name('attendance');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // ユーザー一覧ページへのルート
    Route::get('/users', [UserController::class, 'index'])->name('user.index');

    // 各ユーザーの勤怠表ページへのルート
    Route::get('/users/{id}', [UserController::class, 'show'])->name('user.show');
});

// ログアウトを強制するルート（開発用）
Route::get('/force-logout', function () {
    Auth::logout(); // ユーザーをログアウト
    return redirect('/login'); // ログイン画面にリダイレクト
})->middleware('auth');
