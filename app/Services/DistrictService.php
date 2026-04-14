<?php

namespace App\Services;

use App\Http\Resources\DistrictResource;
use App\Models\District;
use App\Models\Province;

class DistrictService extends ResponseService
{

    public function getDistrict()
    {
        $page = max(1, (int) request()->get('page', 1));
        $limit = max(1, (int) request()->get('limit', 40));

        // Use eager loading for query data
        $query = District::orderBy('districts.created_at', 'desc');

        // Filter by params
        if (request()->hasAny(['province_code', 'name'])) {
            $query->when(request('province_code'), function ($q, $districtCode) {
                $q->where('districts.province_code', $districtCode);
            })
                ->when(request('name'), function ($q, $name) {
                    $q->where('districts.name', 'like', '%' . $name . '%');
                });
        }
        // Paginate data
        $districts = $query->paginate($limit, ['districts.*'], 'page', $page);

        // Check if data is empty
        if ($districts->isEmpty()) {
            return $this->errorListJsonResponse(404, null, 'Kabupaten/ Kota tidak ditemukan');
        }

        // Response JSON
        return $this->listJsonResponse(200, null, 'Berhasil menampilkan data Kabupaten/ kota', DistrictResource::collection($districts));
    }

    public function storeDistrict($request, $province_code)
    {
        $data = $request->validate([
            'code' => 'required|unique:districts,code',
            'name' => 'required|unique:districts,name',
        ], [
            'code.required' => 'Kode kabupaten/ kota harus diisi',
            'code.unique' => 'Kode kabupaten/ kota sudah ada',
            'name.required' => 'Nama kabupaten/ kota harus diisi',
            'name.unique' => 'Nama kabupaten/ kota sudah ada',
        ]);

        $district = new District();
        $district->province_code = $province_code;
        $district->code = $data['code'];
        $district->name = $data['name'];

        if ($district->save()) {
            return $this->createdJsonResponse(201, null, 'Data kabupaten/ kota berhasil ditambahkan', new DistrictResource($district));
        }
        return $this->errorJsonResponse(500, null, 'Data kabupaten/ kota gagal ditambahkan');
    }

    public function updateDistrict($request, $province_code, $id)
    {
        $data = $request->validate([
            'code' => 'nullable',
            'name' => 'nullable',
        ]);

        $district = District::find($id);
        if (!$district) {
            return $this->errorJsonResponse(404, null, 'Data kabupaten/ kota tidak ditemukan');
        }
        $district->province_code = $province_code;
        $data['code'] = $data['code'] ?? $district->code;
        $data['name'] = $data['name'] ?? $district->name;
        $district->update($data);
        return  $this->updatedJsonResponse(200, null, 'Data kabupaten/ kota berhasil diubah', new DistrictResource($district));
    }

    public function deleteDistrict($province_code, $id)
    {
        $province = Province::where('code', $province_code)->first();
        if (!$province) {
            return $this->errorJsonResponse(404, null, 'Provinsi tidak ditemukan');
        }
        $district = District::where([
            'id' => $id,
            'province_code' => $province->code,
        ])->first();
        $district->delete();

        return $this->successJsonResponse(200, null, 'Data kabupaten/ kota berhasil dihapus', null);
    }
}
