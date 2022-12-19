<?php

namespace App\Http\Middleware;

use App\Models\VerifyToken;
use Closure;
use Illuminate\Http\Request;

class VerifyUser
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
       // dd($request->all());
        if($request->token){
           if( VerifyToken::where('token',$request->token)->first()){
                return $next($request);
           }         
        }
            return response()->json(['message'=>"token3 not exist".$request->token." no",'status'=>false], 422);     
        
        
    }
}
