<?php

use App\Http\Controllers\PhotoController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\UserAuth;
use App\Http\Middleware\VerifyUser;
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
    Route::get('/verfiy/{hash}',[UserController::class,'verfiyEmail'])->name("verfiy");
    Route::get('/login', [UserController::class,'login'])->middleware(UserAuth::class);
    Route::get('/forgot', [UserController::class,'forgot'])->middleware(UserAuth::class)->name('forgot');
    Route::get('/reset-password/{token}', function ($token) {
        return response()->pfResponce("change password now with this token ".$token,true);
    })->name('password.reset');
    Route::post('/resetpassword', [UserController::class,'restPassword'])
    ->middleware([UserAuth::class])->name('password.update');
    Route::post('/update', [UserController::class,'update'])->middleware(VerifyUser::class)->name('user.update');
    Route::post('/logout', [UserController::class,'logout'])->middleware(VerifyUser::class)->name('user.logout');

    

    //photos section
    Route::prefix('photo')->controller(UserController::class)->group(function () {
        Route::post('/upload', [PhotoController::class,'upload'])->middleware(VerifyUser::class)->name('upload.photos');
        Route::post('/delete', [PhotoController::class,'delete'])->middleware(VerifyUser::class)->name('delete.photos');
        Route::get('/list', [PhotoController::class,'list'])->name('list.photos');
        Route::get('/search', [PhotoController::class,'search'])->middleware(VerifyUser::class)->name('search.photos');
        Route::post('/statusUpdate', [PhotoController::class,'statusUpdate'])->middleware(VerifyUser::class)->name('statusUpdate.photos');
        Route::get('/getShareableLink', [PhotoController::class,'getShareableLink'])->name('getShareableLink.photos');
        Route::get('/imagehosting/photview/{id}', [PhotoController::class,'photview'])->name('photview.photos');

    });


});


















