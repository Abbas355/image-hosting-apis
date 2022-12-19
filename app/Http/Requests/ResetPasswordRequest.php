<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
            $ptokens=null;
            if(hash_equals($this->user->tokens->token,$this->input('token'))){
                $ptokens=$this->user->tokens;
            }
            if((!is_null($ptokens))&& Carbon::parse($ptokens->created_at)->addMinutes(50)->gte(Carbon::now())){
                $this->user->tokens->delete();
                return true;
            }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ];
    }
    
     /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
            'status' => false
          ], 422));
        
    }
}
