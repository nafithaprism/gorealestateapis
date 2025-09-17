<?php

use Illuminate\Foundation\Application;

$app = new Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/**
 * Always use /tmp on Lambda and ensure dirs exist on every cold start.
 * (Cold start = /tmp is empty.)
 */
$app->useStoragePath('/tmp/storage');

// force /tmp/bootstrap (no env dependency)
$app->useBootstrapPath('/tmp/bootstrap');

// make sure /tmp/bootstrap/cache exists and is writable
$cacheDir = '/tmp/bootstrap/cache';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0775, true);
}
@chmod($cacheDir, 0775);

/**
 * Bind Kernels and Exception Handler
 */
$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

return $app;