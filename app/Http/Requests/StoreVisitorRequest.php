<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreVisitorRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'fullname' => 'required|string',
            'photo_visitor' => 'required|mimes:jpg,jpeg,png',
            'address' => 'required|string',
            'visitor_type_id' => 'required|exists:visitor_types,id',
            'objective_id' => 'required|exists:objectives,id',
        ];
    }

    public function messages()
    {
        return [
            'fullname.required' => 'Nama wajib diisi',
            'address.required' => 'Alamat wajib diisi',
            'visitor_type_id.required' => 'Jenis peran wajib diisi',
            'objective_id.required' => 'Tujuan wajib diisi',
            'photo_visitor.required' => 'Gambar wajib diunggah',
            'photo_visitor.mimes' => 'Format gambar harus jpg, jpeg, png',
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
