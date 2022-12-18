<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmailVerificationRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\SignupUserRequest;
use App\Jobs\SendEmailJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Mail\VerfiUserEmail;


class UserController extends Controller
{
    public function add(SignupUserRequest $request){
        $photo=$this->getImagePath($request);
        $request->merge([ 'photo' => $photo ]);
        $user=User::create($request->only(['name','password','email','photo']));
        $tokenLink=route('verfiy', [$user->id,sha1($user->email)]);
        SendEmailJob::dispatch(['email'=>$request->email,'tokenlink'=>$tokenLink,'subject'=>"Verify Email"]); 
        DB::table('password_resets')->insert(['email' => $user->email,'token' => sha1($user->email),'created_at' => Carbon::now()]);
        return response()->pfResponce($user,true);   //using a  macro response 
    }


    public function getImagePath($request){
        if ($request->hasFile('image')) {
             $path = $request->image->store('images');
        }
        return $path;
        
    }




    public function verfiyEmail(EmailVerificationRequest $request,$id){
        $user=User::find($id);
        $message ="user is verifed successfully...";
        $status=false;
        if(! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            DB::table('password_resets')->where('email', $user->email)->delete();
            $status=true;
        }else{
            $message="user is alraedy verified";
        }
        return response()->pfResponce($message,$status);
    }





    public function login(LoginRequest $request){
        $user=$request->user;
        $data="user not found";
        $status=false;
        if ($user && Hash::check($request->input('password'), $user->password)) {
            $user->remember_token=Str::random(20);
            $user->save();
            $data=$user;
            $status=true;
        }
        return response()->pfResponce($data,$status);     
    }


    public function forgot(ForgotPasswordRequest $request){
        $tokenLink=route('password.reset', [sha1($request->input('email'))]);
        SendEmailJob::dispatch(['email'=>$request->email,'tokenlink'=>$tokenLink,'subject'=>"Reset Password Email"]);
        DB::table('password_resets')->insert(['email' => $request->input('email'),'token' => sha1($request->input('email')),'created_at' => Carbon::now()]);
        return response()->pfResponce("forgot token send to your email",true);
    }


    
    public  function restPassword(ResetPasswordRequest $request) {
        $user=$request->user;
        $user->password=$request->input('password');
        $user->save();
        return response()->pfResponce("password successfully reset...",true); 
    }
   



}
