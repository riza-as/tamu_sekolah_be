<?php

namespace App\Services;

use App\Http\Resources\SubdistrictResource;
use App\Models\District;
use App\Models\Subdistrict;

class SubdistrictService extends ResponseService
{
    public function getSubdistrict()
    {
        $page = max(1, (int) request()->get('page', 1));
        $limit = max(1, (int) request()->get('limit', 40));

        // Use eager loading for query data
        $query = Subdistrict::orderBy('subdistricts.created_at', 'desc');

        // Filter by params
        if (request()->hasAny(['district_code', 'name'])) {
            $query->when(request('district_code'), function ($q, $district_code) {
                $q->where('subdistricts.district_code', $district_code);
            })
                ->when(request('name'), function ($q, $name) {
                    $q->where('subdistricts.name', 'like', '%' . $name . '%');
                });
        }

        // Paginate data
        $subdistricts = $query->paginate($limit, ['subdistricts.*'], 'page', $page);

        // Check if data is empty
        if ($subdistricts->isEmpty()) {
            return $this->errorListJsonResponse(404, null, 'Kecamatan tidak ditemukan');
        }

        // Response JSON
        return $this->listJsonResponse(200, null, 'Berhasil menampilkan data kecamatan', SubdistrictResource::collection($subdistricts));
    }

    public function storeSubdistrict($request, $district_code)
    {
        $data = $request->validate([
            'code' => 'required|unique:subdistricts,code',
            'name' => 'required',
        ], [
            'code.required' => 'Kode kecamatan harus diisi',
            'code.unique' => 'Kode kecamatan sudah ada',
            'name.required' => 'Nama kecamatan harus diisi',
            'name.unique' => 'Nama kecamatan sudah ada',
        ]);

        $district = new Subdistrict();
        $district->district_code = $district_code;
        $district->code = $data['code'];
        $district->name = $data['name'];

        if ($district->save()) {
            return $this->createdJsonResponse(201, null, 'Kecamatan berhasil ditambahkan', new SubdistrictResource($district));
        }
        return $this->errorJsonResponse(500, null, 'Kecamatan gagal ditambahkan');
    }

    public function updateSubdistrict($request, $district_code, $id)
    {
        $data = $request->validate([
            'code' => 'nullable',
            'name' => 'nullable',
        ]);

        $district = Subdistrict::find($id);
        if (!$district) {
            return $this->errorJsonResponse(404, null, 'Kecamatan tidak ditemukan');
        }
        $district->district_code = $district_code;
        $data['code'] = $data['code'] ?? $district->code;
        $data['name'] = $data['name'] ?? $district->name;
        $district->update($data);
        return $this->updatedJsonResponse(200, null, 'Kecamatan berhasil diubah', new SubdistrictResource($district));
    }

    public function deleteSubdistrict($district_code, $id)
    {
        $district = District::where('code', $district_code)->first();
        if (!$district) {
            return $this->errorJsonResponse(404, null, 'Kecamatan tidak ditemukan');;
        }
        $district = Subdistrict::where([
            'id' => $id,
            'district_code' => $district->code,
        ])->first();
        $district->delete();

        return $this->successJsonResponse(200, null, 'Data kecamatan berhasil dihapus', null);
    }
}
