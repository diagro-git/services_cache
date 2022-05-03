<?php

use App\Http\Controllers\CacheController;
use App\Http\Middleware\BackendAuthentication;
use App\Http\Middleware\CacheHeaders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware([BackendAuthentication::class])->group(function() {

    Route::controller(CacheController::class)->group(function() {

        Route::get('/cache', 'get')
            ->middleware(CacheHeaders::class);

        Route::post('/cache', 'store')
            ->middleware(CacheHeaders::class);

        Route::delete('/cache', 'remove');
        Route::delete('/user/{user_id}', 'remove');
        Route::delete('/company/{company_id}', 'remove');
        Route::delete('/user/{user_id}/company/{company_id}', 'remove');

    });

});
