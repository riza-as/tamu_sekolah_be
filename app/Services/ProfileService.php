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

        // Use eager loading for query data
        $query = Profile::with('user', 'province', 'district', 'subdistrict', 'village')
            ->leftJoin('users', 'users.id', '=', 'profiles.user_id')
            ->leftJoin('provinces', 'provinces.code', '=', 'profiles.province_code')
            ->leftJoin('districts', 'districts.code', '=', 'profiles.district_code')
            ->leftJoin('subdistricts', 'subdistricts.code', '=', 'profiles.subdistrict_code')
            ->leftJoin('villages', 'villages.code', '=', 'profiles.village_code')
            ->orderBy('profiles.created_at', 'desc');

        // Filter by params
        if (request()->hasAny(['province_code',  'district_code', 'subdistrict_code', 'village_code', 'name', 'level'])) {
            $query->when(request('village_code'), function ($q, $villageCode) {
                $q->where('profiles.village_code', $villageCode);
            })->when(request('village_name'), function ($q, $villageName) {
                $q->where('villages.name', 'ilike', '%' . $villageName . '%');
            })->when(request('name'), function ($q, $name) {
                $q->where('profiles.fullname', 'ilike', '%' . $name . '%');
            })->when(request('level'), function ($q, $level) {
                $q->where('users.level', $level);
            })->when(request('district_code'), function ($q, $districtCode) {
                $q->where('profiles.district_code', $districtCode);
            })->when(request('subdistrict_code'), function ($q, $subdistrictCode) {
                $q->where('profiles.subdistrict_code', $subdistrictCode);
            })->when(request('province_code'), function ($q, $provinceCode) {
                $q->where('profiles.province_code', $provinceCode);
            })->where('profiles.user_id', '!=', $user->id);
        }

        // Paginate data
        $profiles = $query->paginate($limit, ['profiles.*'], 'page', $page);

        // Check if data is empty
        if ($profiles->isEmpty()) {
            return $this->errorListJsonResponse(404, null, 'Data profil tidak ditemukan');
        }

        return $this->listJsonResponse(200, null, 'Berhasil menampilkan data profil', ProfileResource::collection($profiles));
    }

    public function getMyProfile()
    {
        $user = Auth::user();
        $profile = Profile::where('user_id', $user->id)->first();
        if (!$profile) {
            return $this->errorJsonResponse(404, null, 'Data profil tidak ditemukan');
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
        // check if profile already exist
        $profile = Profile::where('user_id', $id)->first();
        if ($profile) {
            return $this->errorJsonResponse(400, null, 'Profil sudah ada');
        }
        // check if user exist
        $user = User::where('id', $id)->first();
        if (!$user->id) {
            return $this->errorJsonResponse(404, null,  'Akun tidak ditemukan');
        }

        // file upload handler
        $photoProfile = null;

        // Cek jika ada file yang diunggah
        if ($request->hasFile('photo_profile')) {
            // Hapus gambar lama jika ada
            if ($profile->photo_profile) {
                $fileName = basename($profile->photo_profile);
                Storage::disk('public')->delete('uploads/photo_profiles/' . $fileName);
            }

            // Proses upload gambar baru
            $photoProfile = uniqid() . '.' . $request->photo_profile->extension();
            $img = Image::make($request->photo_profile->path());

            // Resize jika lebar gambar lebih dari 720px
            if ($img->width() > 720) {
                $img->resize(null, 720, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }

            // Sesuaikan orientasi gambar jika perlu
            $img->orientate();

            // Simpan gambar ke Storage disk 'public'
            $path = 'uploads/photo_profiles/' . $photoProfile;
            Storage::disk('public')->put($path, (string) $img->encode());

            // Update path gambar pada profil
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

        // Cek jika ada file yang diunggah
        if ($request->hasFile('photo_profile')) {
            // Hapus gambar lama jika ada
            if ($profile->photo_profile) {
                $fileName = basename($profile->photo_profile);
                Storage::disk('public')->delete('uploads/photo_profiles/' . $fileName);
            }

            // Proses upload gambar baru
            $photoProfile = uniqid() . '.' . $request->photo_profile->extension();
            $img = Image::make($request->photo_profile->path());

            // Resize jika lebar gambar lebih dari 720px
            if ($img->width() > 720) {
                $img->resize(null, 720, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }

            // Sesuaikan orientasi gambar jika perlu
            $img->orientate();

            // Simpan gambar ke Storage disk 'public'
            $path = 'uploads/photo_profiles/' . $photoProfile;
            Storage::disk('public')->put($path, (string) $img->encode());

            // Update path gambar pada profil
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
            'school_code' => $request->school_code ?? $profile->school_code,
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
