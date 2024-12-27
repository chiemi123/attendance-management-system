<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 必要に応じてbootメソッドを強制的に呼び出す
        $this->boot();
        Schema::defaultStringLength(191);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::addLocation(base_path('src/resources/views'));

        // 検索パスをログに出力して確認
        Log::info('View paths: ', View::getFinder()->getPaths());
    }
}
