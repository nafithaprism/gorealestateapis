<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\MailManager;

class MailForceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Ensure the binding exists even if the framework’s deferred provider didn’t load.
        $this->app->singleton('mail.manager', function ($app) {
            return new MailManager($app);
        });

        // Optional aliases (nice to have)
        $this->app->alias('mail.manager', MailManager::class);
    }

    public function boot(): void
    {
        //
    }
}