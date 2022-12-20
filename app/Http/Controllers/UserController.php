<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmailVerificationRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProfileUpdateRequest;
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
use App\Models\VerifyToken;
use Illuminate\Support\Facades\Crypt;

class UserController extends Controller
{
    public function add(SignupUserRequest $request){
        $photo=$this->getImagePath($request);
        $request->merge([ 'photo' => $photo ]);
        $user=User::create($request->only(['name','password','email','photo','age']));
        $tokenLink=route('verfiy', [Crypt::encryptString($user->id)]);
        SendEmailJob::dispatch(['email'=>$request->email,'tokenlink'=>$tokenLink,'subject'=>"Verify Email"]); 
        $user->tokens()->save(new VerifyToken(['token' => Crypt::encryptString($user->id)]));
        return response()->pfResponce($user,true);   //using a  macro response 
    }


    public function getImagePath($request){
        $path=null;
        if ($request->hasFile('image')) {
             $path = $request->image->store('images');
        }
        return $path;
        
    }




    public function verfiyEmail(EmailVerificationRequest $request,$hash){
        
        $user=User::find(Crypt::decryptString($hash));
        $message ="user is verifed successfully...";
        $status=false;
        if(! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            $user->tokens->delete();
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
        $api_token=Str::random(20);
        if ($user && Hash::check($request->input('password'), $user->password)) {
            // $user->remember_token=$api_token;
            // $user->save();
            $user->tokens()->save(new VerifyToken(['token' => $api_token]));
            $data=$user;
            $data['api_token']=$api_token;
            $status=true;
        }
        return response()->pfResponce($data,$status);     
    }


    public function forgot(ForgotPasswordRequest $request){
        $user=User::where('email',$request->input('email'))->first();
        $token=Crypt::encryptString($user->id);
        $tokenLink=route('password.reset', [$token]);
        SendEmailJob::dispatch(['email'=>$request->email,'tokenlink'=>$tokenLink,'subject'=>"Reset Password Email"]);
       // DB::table('password_resets')->insert(['email' => $request->input('email'),'token' => sha1($request->input('email')),'created_at' => Carbon::now()]);
       $user->tokens()->save(new VerifyToken(['token' => $token])); 
       return response()->pfResponce("forgot token send to your email",true);
    }


    
    public  function restPassword(ResetPasswordRequest $request) {
        $user=$request->user;
        $user->password=$request->input('password');
        $user->save();
        return response()->pfResponce("password successfully reset...",true); 
    }


    public  function update(ProfileUpdateRequest $request) {
        $user=VerifyToken::where('token',$request->token)->first()->user;
        $photo=$this->getImagePath($request);
        if(!is_null($photo)) $request->merge([ 'photo' => $photo ]);
        $user->update($request->only(['name','password','photo','age']));
        return response()->pfResponce("user update successfully ...",true); 
    }

    
    public  function logout(Request $request) {
        $vtoken=VerifyToken::where('token',$request->token);
        $vtoken->delete();
        return response()->pfResponce("user logout successfully ...",true); 
    }

   



}
