<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Models\Profile;
use App\Models\QrCode;
use App\Models\SubdistrictQrCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserService extends ResponseService
{
    public function getUserList()
    {
        $user = Auth::user();
        $page = request()->input('page');
        $limit = request()->input('limit', 10);

        // get user by params village_code, username, level
        if (request()->hasAny(['village_code', 'username', 'level'])) {
            $villageCode = request()->input('village_code');
            $username = request()->input('username');
            $level = request()->input('level');
            $users = User::select('users.*')
                // use leftJoin to get profile if user dont have profile, it will return null
                ->leftJoin('profiles', 'profiles.user_id', '=', 'users.id')
                ->when($villageCode, function ($q) use ($villageCode) {
                    $q->where('profiles.village_code', $villageCode);
                })
                ->when($username, function ($q) use ($username) {
                    $q->where('users.username', 'ilike', '%' . $username . '%');
                })
                ->when($level, function ($q) use ($level) {
                    $q->where('users.level', $level);
                })
                ->where('users.id', '!=', $user->id)
                ->paginate($limit, ['*'], 'page', $page);
            if ($users->isEmpty()) {
                return $this->errorListJsonResponse(404,  null, 'Data user tidak ditemukan');
            }
            return $this->listJsonResponse(200, null, 'Berhasil menampilkan data user', UserResource::collection($users));
        } else {

            // get all user
            $users = User::paginate($limit, ['*'], 'page', $page);
            if ($users->isEmpty()) {
                return $this->errorListJsonResponse(404,  null, 'Data user tidak ditemukan');
            }
            return $this->listJsonResponse(200, null, 'Berhasil menampilkan semua data user', UserResource::collection($users));
        }
    }

    public function getMyUser()
    {
        $user = Auth::user();
        return $this->successJsonResponse(200, null, 'Berhasil menampilkan detail data user anda', new UserResource($user));
    }
    public  function getUserDetail($id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorJsonResponse(404,  null, 'Data user tidak ditemukan');
        }
        return $this->successJsonResponse(200, null, 'Berhasil menampilkan detail data user', new UserResource($user));
    }

    public function storeUser($request)
    {
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'level' => $request->level ?? 2,
        ]);
        $user->save();
        Profile::create([
            'user_id' => $user->id,
            'fullname' => $user->username,
            'province_code' => 0,
            'district_code' => 0,
            'subdistrict_code' => 0,
            'village_code' => 0,
            'school_code' => $request->school_code ?? null,
        ]);

        return $this->createdJsonResponse(201, null, 'Berhasil menambahkan data user', new UserResource($user));
    }

    public function updateUser(Request $request, $id)
    {
        // check if user exist
        $user = User::where('id', $id)->first();
        if (!$user) {
            return $this->errorJsonResponse(404,  null, 'Data user tidak ditemukan');
        }

        // check unique nik and npwp
        if ($request->username && $request->username !== $user->username) {
            $existUsername = User::where('username', $request->username)->first();
            if ($existUsername) {
                return response()->json([
                    'code' => 422,
                    'message' => [
                        'username' => 'User sudah digunakan'
                    ],
                ], 422);
            }
        }

        // user data allowed
        $user->username = $request->username ?? $user->username;
        $user->email = $request->email ?? $user->email;
        $password = $request->password;
        if ($password) {
            $user->password = bcrypt($password);
        }

        $user->save();
        return $this->updatedJsonResponse(200, null, 'Akun berhasil diperbarui', UserResource::make($user));
    }

    public function activateUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->errorJsonResponse(404,  null, 'Data user tidak ditemukan');
        }

        // toggle status aktif/nonaktif
        $user->is_active = $user->is_active == 1 ? 0 : 1;
        $user->save();

        // update qr code status
        if ($user->level == 2) {
            $qr_code = QrCode::where('village_code', $user->profile->village_code)
                ->update(['status' => $user->is_active]);
        }

        // update qr code status
        if ($user->level == 3) {
            $subdistrict_qr_code = SubdistrictQrCode::where('subdistrict_code', $user->profile->subdistrict_code)
                ->update(['status' => $user->is_active]);
        }

        if ($user->level == 4 && $user->profile?->school_code) {
            QrCode::where(
                'school_code',
                $user->profile->school_code
            )->update([
                'status' => $user->is_active
            ]);
        }

        $message = $user->is_active
            ? 'Akun berhasil diaktifkan'
            : 'Akun berhasil dinonaktifkan';

        return $this->successJsonResponse(200, null, $message, UserResource::make($user));
    }

    public function destroyUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorJsonResponse(404,  null, 'Data user tidak ditemukan');
        }

        // if has profile, delete profile
        if ($user->profile) {
            $user->profile->delete();
        } else {
            $profile = Profile::where('user_id', $id)->first();
            if ($profile) {
                $profile->delete();
            }
        }

        $user->delete();
        return $this->successJsonResponse(200, null, 'Berhasil menghapus data user', null);
    }
}
