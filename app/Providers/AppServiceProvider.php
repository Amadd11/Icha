<?php

namespace App\Providers;

use App\Listeners\SaveSentEmailToHostingerSentFolder;
use App\Mail\Transports\ResendTransport;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
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
            $key = (string) config('services.resend.key');
            return new ResendTransport($key);
        });

        // Automatically mirror sent emails into Hostinger's Sent folder
        Event::listen(MessageSent::class, SaveSentEmailToHostingerSentFolder::class);
    }
}
