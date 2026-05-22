<?php

namespace App\Services;

use App\Models\School;
use App\Http\Resources\SchoolResource;

class SchoolService extends ResponseService
{
    public function getSchool()
    {
        $query = School::with([
            'village.subdistrict.district.province',
            'level',
            'status'
        ]);

        //filter by province_code
        $query->when(request('province_code'), function ($q) {
            $q->whereHas('village.subdistrict.district.province', function ($q2) {
                $q2->where('code', request('province_code'));
            });
        });

        //filter by district_code
        $query->when(request('district_code'), function ($q) {
            $q->whereHas('village.subdistrict.district', function ($q2) {
                $q2->where('code', request('district_code'));
            });
        });

        //filter by subdistrict_code
        $query->when(request('subdistrict_code'), function ($q) {
            $q->whereHas('village.subdistrict', function ($q2) {
                $q2->where('code', request('subdistrict_code'));
            });
        });

        //filter by village_code
        $query->when(request('village_code'), function ($q) {
            $q->whereHas('village', function ($q2) {
                $q2->where('code', request('village_code'));
            });
        });

        //filter by level_id
        $query->when(request('level_id'), function ($q) {
            $q->where('level_id', request('level_id'));
        });

        //filter by status_id
        $query->when(request('status_id'), function ($q) {
            $q->where('status_id', request('status_id'));
        });

        $schools = $query->get();

        return $this->successJsonResponse(
            200,
            null,
            'Berhasil menampilkan data sekolah',
            SchoolResource::collection($schools)
        );
    }

    public function showSchool($id)
    {
        $school = School::with(['level', 'status'])
            ->where('school_code', $id)
            ->first();

        if (!$school) {
            return $this->errorJsonResponse(
                404,
                null,
                'Data sekolah tidak ditemukan'
            );
        }

        return $this->successJsonResponse(
            200,
            null,
            'Berhasil menampilkan detail sekolah',
            new SchoolResource($school)
        );
    }

    public function storeSchool($request)
    {
      
        $request->validate([
            'school_code'  => 'required|unique:schools,school_code',
            'name'         => 'required',
            'address'      => 'required',
            'village_code' => 'required',
            'level_id'     => 'required',
            'status_id'    => 'required',
        ]);

        // Sekarang simpan datanya
        $school = School::create([
            'name'         => $request->name,
            'address'      => $request->address,
            'village_code' => $request->village_code,
            'school_code'  => $request->school_code,
            'level_id'     => $request->level_id,
            'status_id'    => $request->status_id,
        ]);

        return $this->createdJsonResponse(
            201,
            null,
            'Berhasil menambahkan sekolah',
            new SchoolResource($school)
        );
    }

    public function updateSchool($request, $school_code)
    {
        $school = School::where('school_code', $school_code)->first();

        if (!$school) {
            return $this->errorJsonResponse(
                404,
                null,
                'Data sekolah tidak ditemukan'
            );
        }

        $school->update([
            'name' => $request->name ?? $school->name,
            'address' => $request->address ?? $school->address,
            'village_code' => $request->village_code ?? $school->village_code,
            'level_id' => $request->level_id ?? $school->level_id,
            'status_id' => $request->status_id ?? $school->status_id,
        ]);

        return $this->updatedJsonResponse(
            200,
            null,
            'Berhasil update sekolah',
            new SchoolResource($school)
        );
    }

    public function destroySchool($school_code)
    {
        $school = School::where('school_code', $school_code)->first();

        if (!$school) {
            return $this->errorJsonResponse(
                404,
                null,
                'Data sekolah tidak ditemukan'
            );
        }

        $school->delete();

        return $this->successJsonResponse(
            200,
            null,
            'Berhasil menghapus sekolah'
        );
    }
}
