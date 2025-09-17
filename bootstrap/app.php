<?php

use Illuminate\Foundation\Application;

$app = new Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

// Use /tmp for storage on Lambda
$app->useStoragePath('/tmp/storage');

// Ensure the runtime cache dir exists on every cold start
$cacheDir = '/tmp/bootstrap/cache';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0775, true);
}
@chmod($cacheDir, 0775);

// Usual bindings...
$app->singleton(Illuminate\Contracts\Http\Kernel::class, App\Http\Kernel::class);
$app->singleton(Illuminate\Contracts\Console\Kernel::class, App\Console\Kernel::class);
$app->singleton(Illuminate\Contracts\Debug\ExceptionHandler::class, App\Exceptions\Handler::class);

return $app;