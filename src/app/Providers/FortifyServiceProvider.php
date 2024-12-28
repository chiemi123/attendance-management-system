<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Registered; // 登録イベントを使用
use Illuminate\Support\Facades\Log; // ログ記録

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */

    public function boot(): void
    {
        // ユーザー登録時にメール認証を送信
        Fortify::createUsersUsing(CreateNewUser::class);
        

        // ログイン処理をカスタマイズしてメール認証済みか確認
        Fortify::authenticateUsing(function (Request $request) {
            // 認証を試みる
           if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
               $user = Auth::user(); // 認証成功後にユーザーを取得

           // メール認証済みか確認
           if ($user->email_verified_at) {
            $request->session()->regenerate();
            return $user;  // ユーザーオブジェクトを返す
            }

            // メール未認証の場合はログアウトさせる
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => __('Your email address is not verified.'),
            ]);
        }

        // 認証失敗時
        throw ValidationException::withMessages([
            'email' => __('These credentials do not match our records.'),
        ]);
    });

        // 登録ページ・ログインページのビュー
        Fortify::registerView(function () {
            return view('auth.register');
        });

        Fortify::loginView(function () {
            return view('auth.login');
        });

        // レートリミッターの設定
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(10)->by($email . $request->ip());
        });
    }

}