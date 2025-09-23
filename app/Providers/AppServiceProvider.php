<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\MailManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
          $this->app->register(\Illuminate\Mail\MailServiceProvider::class);

        // Fallback (belt & suspenders): manually bind the manager
        $this->app->singleton('mail.manager', function ($app) {
            return new MailManager($app);
        });
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}