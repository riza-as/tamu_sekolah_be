<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\Validator ;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() != null;
    }

    public function rules()
    {
        return [
            // 'username' => 'nullable|unique:users|max:100|regex:/^\S*$/u',
            // 'email' => 'nullable|email',
            'password' => 'nullable|min:5|regex:/^\S*$/u|confirmed',

        ];
    }

    public function messages()
    {
        return [
            'username.unique' => 'Username sudah digunakan',
            'username.regex' => 'Username tidak boleh mengandung spasi',
            'email.unique' => 'Email sudah digunakan',
            'password.min' => 'Password minimal 5 karakter',
            'password.regex' => 'Password harus terdiri dari huruf dan angka',
            'password.confirmed' => 'Konfirmasi password tidak sama',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'code' => 422,
            'message' => $validator->errors(),
        ], 422));
    }
}
