<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService extends ResponseService
{
    public function authLogin($data)
    {
         if (!isset($data['username']) || !isset($data['password'])) {
            return response()->json([
                'code' => 400,
                'status' => 'failed',
                'message' => 'Username dan Password tidak boleh kosong',
            ], 400);
        }

        // Cek apakah user dengan username tersebut ada
        $user = User::where('username', $data['username'])->first();

        // Cek apakah user aktif
        if ($user->is_active === 0) {
            return response()->json([
                'code' => 400,
                'status' => 'failed',
                'message' => 'Maaf, akun anda sudah tidak aktif',
            ], 400);
        }
        // Jika user tidak ditemukan, kembalikan respons failed
        if (!$user) {
            return response()->json([
                'code' => 400,
                'status' => 'failed',
                'message' => 'Username atau Password salah',
            ], 400);
        }

        // Periksa apakah password benar
        if (!Hash::check($data['password'], $user->password)) {
            throw new HttpResponseException(response()->json([
                'code' => 400,
                'status' => 'failed',
                'message' => 'Username atau password salah',
            ], 400));
        }

        // Pastikan hanya mengirimkan username & password ke JWTAuth::attempt()
        $credentials = [
            'username' => $data['username'],
            'password' => $data['password']
        ];

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'code' => 400,
                'status' => 'failed',
                'message' => 'Autentikasi gagal',
            ], 400);
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Berhasil masuk',
            'data' => new UserResource($user),
            'access_token' => $token,
        ]);
    }

    public function logout()
    {
        $token = JWTAuth::getToken();
        if (!$token) {
            return response()->json([
                'code' => 401,
                'status' => false,
                'message' => 'No token provided',
            ], 401);
        }

        $removeToken = JWTAuth::invalidate($token);
        if ($removeToken) {
            return response()->json([
                'code' => 200,
                'status' => true,
                'message' => 'Keluar berhasil',
            ], 200);
        }

        return response()->json([
            'code' => 500,
            'status' => false,
            'message' => 'Keluar gagal',
        ], 500);
    }
}
