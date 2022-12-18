<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class EmailVerificationRequest extends FormRequest
{  
    
    
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (! hash_equals((string) $this->route('hash'),
                          sha1($this->getsUser()->getEmailForVerification()))) {
            return false;
        }
        return $this->expireToken();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            //
        ];
    }
    public function expireToken()
    {
       $token= DB::table('password_resets')->where('email', $this->getsUser()->email)->first();
       if((!is_null($token)) &&Carbon::parse($token->created_at)->addMinutes(50)->gte(Carbon::now())){
           return true;
       }
       DB::table('password_resets')->where('email', $this->getsUser()->email)->delete();
       return false;
       
    }

    public function getsUser(){
      return  User::find($this->route('id'));
    }
   
}
