<?php

namespace App\Http\Middleware;

use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
       
        $user=User::where('email',$request->email)->first();
        if($user){
            if($user->email_verified_at==null){
                return response()->json(['message'=>"please first verify",'status'=>false], 422);
            }
        }else{
             return response()->json(['message'=>"user not found45",'status'=>false], 422);
        }
       
        $request->merge(['user' => $user,]);
        return $next($request);
    }
}
