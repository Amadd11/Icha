<?php

namespace App\Providers;

use App\Mail\Transports\ResendTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
        Vite::prefetch(concurrency: 3);

        Mail::extend('resend', function () {
            $key = config('services.resend.key') ?: env('RESEND_API_KEY');
            return new ResendTransport($key);
        });
    }
}
