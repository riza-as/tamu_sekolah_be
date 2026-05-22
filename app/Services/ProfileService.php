<?php

namespace App\Services;

use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class ProfileService extends ResponseService
{
    public function getProfileList()
    {
        $user = Auth::user();

        $page = max(1, (int) request()->get('page', 1));
        $limit = max(1, (int) request()->get('limit', 40));

        $query = Profile::with(['user', 'province', 'district', 'subdistrict', 'village', 'school'])
            ->orderBy('created_at', 'desc');

        if (request()->hasAny(['province_code', 'district_code', 'subdistrict_code', 'village_code', 'name', 'level', 'village_name'])) {

            $query->when(request('village_code'), function ($q, $villageCode) {
                $q->where('village_code', $villageCode);
            })
                ->when(request('village_name'), function ($q, $villageName) {
                    $q->whereHas('village', function ($queryRelation) use ($villageName) {
                        $queryRelation->where('name', 'ilike', '%' . $villageName . '%');
                    });
                })
                ->when(request('name'), function ($q, $name) {
                    $q->where('fullname', 'ilike', '%' . $name . '%');
                })
                ->when(request('level'), function ($q, $level) {
                    $q->whereHas('user', function ($queryRelation) use ($level) {
                        $queryRelation->where('level', $level);
                    });
                })
                ->when(request('district_code'), function ($q, $districtCode) {
                    $q->where('district_code', $districtCode);
                })
                ->when(request('subdistrict_code'), function ($q, $subdistrictCode) {
                    $q->where('subdistrict_code', $subdistrictCode);
                })
                ->when(request('province_code'), function ($q, $provinceCode) {
                    $q->where('province_code', $provinceCode);
                });
        }

    
        $query->where('user_id', '!=', $user->id);

      
        $profiles = $query->paginate($limit, ['*'], 'page', $page);

        if ($profiles->isEmpty()) {
            return $this->errorListJsonResponse(404, null, 'Data profil tidak ditemukan');
        }

        return $this->listJsonResponse(200, null, 'Berhasil menampilkan data profil', ProfileResource::collection($profiles));
    }

    public function getMyProfile()
    {
        $user = Auth::user();

        if (!$user) {
            return $this->errorJsonResponse(401, null, 'User tidak terautentikasi');
        }

        if ($user->id === 1 || $user->role === 'super_admin') {
            $superAdminProfile = new \stdClass();
            $superAdminProfile->id = 0;
            $superAdminProfile->user_id = $user->id;
            $superAdminProfile->fullname = 'Super Admin System';
            $superAdminProfile->school_code = null;
            $superAdminProfile->subdistrict_code = null;
            $superAdminProfile->district_code = null;
            $superAdminProfile->province_code = null;

            return $this->successJsonResponse(200, null, 'Berhasil menampilkan detail data profil Super Admin', $superAdminProfile);
        }
        $profile = Profile::where('user_id', $user->id)->first();
        if (!$profile) {
            return $this->errorJsonResponse(404, null, 'Data profil tidak ditemukan untuk User ID: ' . $user->id);
        }

        return $this->successJsonResponse(200, null, 'Berhasil menampilkan detail data profil', new ProfileResource($profile));
    }

    public function getProfileDetail($id)
    {
        $profile = Profile::find($id);
        if (!$profile) {
            return $this->errorJsonResponse(404, null, 'Data profil tidak ditemukan');
        }
        return $this->successJsonResponse(200, null, 'Berhasil menampilkan detail data profil', new ProfileResource($profile));
    }

    public function storeProfile($request, $id)
    {

        $profile = Profile::where('user_id', $id)->first();
        if ($profile) {
            return $this->errorJsonResponse(400, null, 'Profil sudah ada');
        }

        $user = User::where('id', $id)->first();
        if (!$user->id) {
            return $this->errorJsonResponse(404, null,  'Akun tidak ditemukan');
        }

        $photoProfile = null;

        if ($request->hasFile('photo_profile')) {
            if ($profile->photo_profile) {
                $fileName = basename($profile->photo_profile);
                Storage::disk('public')->delete('uploads/photo_profiles/' . $fileName);
            }

            $photoProfile = uniqid() . '.' . $request->photo_profile->extension();
            $img = Image::make($request->photo_profile->path());

            if ($img->width() > 720) {
                $img->resize(null, 720, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }

            $img->orientate();

            $path = 'uploads/photo_profiles/' . $photoProfile;
            Storage::disk('public')->put($path, (string) $img->encode());

            $photoProfileUrl = Storage::url($path);
            $profile->photo_profile = $photoProfileUrl;
            $data_request['photo_profile'] = $photoProfileUrl;
        } else {
            $data_request['photo_profile'] = $photoProfile;
        }
        $profile = Profile::create([
            'user_id' => $user->id,
            'fullname' => $request->fullname,
            'photo_profile' => $photoProfile,
            'province_code' => $request->province_code,
            'district_code' => $request->district_code,
            'subdistrict_code' => $request->subdistrict_code,
            'village_code' => $request->village_code,
        ]);
        if (!$profile) {
            return $this->errorJsonResponse(400, null, 'Gagal menambahkan data profil');
        }
        return $this->createdJsonResponse(201, null, 'Berhasil menambahkan data profil', new ProfileResource($profile));
    }

    public function updateProfile($request, $id)
    {
        $profile = Profile::find($id);
        if (!$profile) {
            return $this->errorJsonResponse(404, null, 'Data profil tidak ditemukan');
        }

        $photoProfile = $profile->photo_profile;

        if ($request->hasFile('photo_profile')) {
            if ($profile->photo_profile) {
                $fileName = basename($profile->photo_profile);
                Storage::disk('public')->delete('uploads/photo_profiles/' . $fileName);
            }

            $photoProfile = uniqid() . '.' . $request->photo_profile->extension();
            $img = Image::make($request->photo_profile->path());

            if ($img->width() > 720) {
                $img->resize(null, 720, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }

            $img->orientate();

            $path = 'uploads/photo_profiles/' . $photoProfile;
            Storage::disk('public')->put($path, (string) $img->encode());

            $photoProfileUrl = Storage::url($path);
            $profile->photo_profile = $photoProfileUrl;
            $data_request['photo_profile'] = $photoProfileUrl;
        } else {
            $data_request['photo_profile'] = $photoProfile;
        }
        $profile->update([
            'fullname' => $request->fullname ?? $profile->fullname,
            // 'photo_profile' => url($photoProfile) ?? $profile->photo_profile,
            'province_code' => $request->province_code ?? $profile->province_code,
            'district_code' => $request->district_code ?? $profile->district_code,
            'subdistrict_code' => $request->subdistrict_code ?? $profile->subdistrict_code,
            'village_code' => $request->village_code ?? $profile->village_code,
            'school_code'      => $request->has('school_code') && $request->school_code != ''
                ? $request->school_code
                : $profile->school_code,
        ]);
        return $this->updatedJsonResponse(200, null, 'Berhasil memperbarui data profil', new ProfileResource($profile));
    }

    public function deleteProfile($id)
    {
        $profile = Profile::find($id);
        if (!$profile) {
            return $this->errorJsonResponse(404, null, 'Data profil tidak ditemukan');
        }
        $profile->delete();
        return $this->successJsonResponse(200, null, 'Berhasil menghapus data profil');
    }
}
