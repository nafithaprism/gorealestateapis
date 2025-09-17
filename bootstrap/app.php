<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
*/

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Use /tmp on AWS Lambda (writable filesystem)
|--------------------------------------------------------------------------
| APP_STORAGE=/tmp is already in your Lambda env. We also point the
| bootstrap path to /tmp/bootstrap and ensure /tmp/bootstrap/cache exists.
*/
$app->useStoragePath(env('APP_STORAGE', $app->basePath('storage')));

$bootstrapPath = env('APP_BOOTSTRAP_PATH', $app->basePath('bootstrap'));
$app->useBootstrapPath($bootstrapPath);

// Ensure /tmp/bootstrap/cache exists so PackageManifest & caches can write
//caches can write
$cacheDir = rtrim($bootstrapPath, '/').'/cache';
if (! is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
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

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
*/

return $app;
