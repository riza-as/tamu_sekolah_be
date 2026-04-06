<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'username' => 'required|unique:users|regex:/^\S*$/u',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:5|confirmed|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
        ];
    }

    public function messages()
    {
        return [
            'username.required' => 'Username tidak boleh kosong',
            'username.unique' => 'Username sudah digunakan',
            'username.regex' => 'Username tidak boleh mengandung spasi',
            'email.email' => 'Format email tidak valid',
            'email.required' => 'Email tidak boleh kosong',
            'email.unique' => 'Email sudah digunakan',
            'password.min' => 'Password minimal 5 karakter',
            'password.required' => 'Password tidak boleh kosong',
            'password.regex' => 'Password harus terdiri dari huruf dan angka',
            'password.confirmed' => 'Konfirmasi Password tidak cocok',
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
