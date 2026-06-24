<?php

use App\Http\Controllers\IclockController;
use Illuminate\Support\Facades\Route;

Route::prefix('iclock')->group(function () {
    Route::match(['get', 'post'], '/cdata', [IclockController::class, 'cdata']);
    Route::get('/getrequest', [IclockController::class, 'getRequest']);
    Route::match(['get', 'post'], '/devicecmd', [IclockController::class, 'deviceCmd']);
    Route::match(['get', 'post'], '/registry', [IclockController::class, 'registry']);
});
