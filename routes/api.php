<?php

use App\Http\Controllers\UserController;
use App\Http\Middleware\UserAuth;
use App\Http\Requests\EmailVerificationRequest as RequestsEmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

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




Route::prefix('user')->controller(UserController::class)->group(function () {
    Route::post('/add', [UserController::class,'add']);
    Route::get('/verfiy/{id}/{hash}',[UserController::class,'verfiyEmail'])->name("verfiy");
    Route::get('/login', [UserController::class,'login'])->middleware(UserAuth::class);
    Route::get('/forgot', [UserController::class,'forgot'])->middleware(UserAuth::class)->name('forgot');
    Route::get('/reset-password/{token}', function ($token) {
        return response()->pfResponce("change password now with this token ".$token,true);
    })->name('password.reset');
    Route::post('/resetpassword', [UserController::class,'restPassword'])
    ->middleware([UserAuth::class])->name('password.update');
});


















