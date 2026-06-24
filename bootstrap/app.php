<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$localAppData = getenv('LOCALAPPDATA') ?: '';

if (PHP_OS_FAMILY === 'Windows' && $localAppData !== '') {
    $localDataBase = $localAppData.DIRECTORY_SEPARATOR.'absensi-rs';
    $localBootstrapCache = $localDataBase.DIRECTORY_SEPARATOR.'bootstrap-cache';
    $localStorage = $localDataBase.DIRECTORY_SEPARATOR.'storage';

    foreach ([
        $localBootstrapCache,
        $localStorage,
        $localStorage.DIRECTORY_SEPARATOR.'app',
        $localStorage.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'data',
        $localStorage.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'sessions',
        $localStorage.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'views',
        $localStorage.DIRECTORY_SEPARATOR.'logs',
    ] as $dir) {
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    $_ENV['LARAVEL_STORAGE_PATH'] = $localStorage;
    $_ENV['APP_PACKAGES_CACHE'] = $localBootstrapCache.DIRECTORY_SEPARATOR.'packages.php';
    $_ENV['APP_SERVICES_CACHE'] = $localBootstrapCache.DIRECTORY_SEPARATOR.'services.php';
    putenv('LARAVEL_STORAGE_PATH='.$localStorage);
    putenv('APP_PACKAGES_CACHE='.$_ENV['APP_PACKAGES_CACHE']);
    putenv('APP_SERVICES_CACHE='.$_ENV['APP_SERVICES_CACHE']);
}

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::group([], base_path('routes/iclock.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->statefulApi();
        $middleware->validateCsrfTokens(except: [
            'iclock/*',
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\CheckModuleMaintenance::class,
        ]);
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/dashboard');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();

if (PHP_OS_FAMILY === 'Windows' && $localAppData !== '') {
    $app->addAbsoluteCachePathPrefix('C:');
}

return $app;
