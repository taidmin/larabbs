<?php

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

use Illuminate\Http\Request;

Route::prefix('v1')->namespace('Api')
                         ->name('api.v1.')
                         ->group(function (){

         // 登录相关的
         Route::middleware('throttle:'. config('api.rate_limits.sign'))->group(function(){
             // 短信验证码
             Route::post('verificationCodes','VerificationCodesController@store')
                 ->name('verificationCodes.store');

             // 用户注册
             Route::post('users', 'UsersController@store')
                 ->name('users.store');
         });

         Route::middleware('throttle:'. config('api.rate_limits.access'))->group(function(){

         });

});
