<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Tambahkan ini

class AppServiceProvider extends ServiceProvider
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
        // Memaksa Laravel menggunakan HTTPS (Sangat penting saat diakses via Ngrok / Hosting)
        if (request()->secure() || str_contains(config('app.url'), 'https://') || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            URL::forceScheme('https');
        }
        
        // Trik khusus untuk ngrok
        if(str_contains(request()->getHost(), 'ngrok')) {
            URL::forceScheme('https');
        }
    }
}