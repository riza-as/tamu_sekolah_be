<?php

namespace App\Services;

use App\Http\Resources\VillageResource;
use App\Models\Subdistrict;
use App\Models\Village;

class VillageService extends ResponseService
{
    public function getVillage()
    {
        $page = max(1, (int) request()->get('page', 1));
        $limit = max(1, (int) request()->get('limit', 40));

        // Use eager loading for query data
        $query = Village::orderBy('villages.created_at', 'desc');

        // Filter by params
        if (request()->hasAny(['subdistrict_code', 'name'])) {
            $query->when(request('subdistrict_code'), function ($q, $districtCode) {
                $q->where('villages.subdistrict_code', $districtCode);
            })
                ->when(request('name'), function ($q, $name) {
                    $q->where('villages.name', 'like', '%' . $name . '%');
                });
        }

        // Paginate data
        $villages = $query->paginate($limit, ['villages.*'], 'page', $page);

        // Check if data is empty
        if ($villages->isEmpty()) {
            return $this->errorListJsonResponse(404, null, 'Desa/ kelurahan tidak ditemukan');
        }

        // Response JSON
        return $this->listJsonResponse(200, null, 'Berhasil menampilkan data desa/ kelurahan', VillageResource::collection($villages));
    }

    public function getVillageById($id)
    {
        $village = Village::where('code', $id)->first();
        if (!$village) {
            return $this->errorJsonResponse(404, null, 'Desa/ kelurahan tidak ditemukan');
        }
        return $this->successJsonResponse(200, null, 'Berhasil menampilkan data desa/ kelurahan', new VillageResource($village));
    }
    public function storeVillage($request, $subdistrict_code)
    {
        $data = $request->validate([
            'code' => 'required|unique:villages,code',
            'name' => 'required',
        ], [
            'code.required' => 'kode desa harus diisi',
            'code.unique' => 'kode desa sudah ada',
            'name.required' => 'nama desa harus diisi',
            'name.unique' => 'nama desa sudah ada',
        ]);

        $village = new Village();
        $village->subdistrict_code = $subdistrict_code;
        $village->code = $data['code'];
        $village->name = $data['name'];

        if ($village->save()) {
            return $this->createdJsonResponse(201, null, 'Desa berhasil ditambahkan', new VillageResource($village));
        }
        return $this->errorJsonResponse(500, null, 'Desa gagal ditambahkan');
    }

    public function updateVillage($request, $subdistrict_code, $id)
    {
        $data = $request->validate([
            'code' => 'nullable',
            'name' => 'nullable',
        ]);

        $village = Village::find($id);
        if (!$village) {
            return response()->json([
                'code' => 404,
                null,
                'message' => 'Desa tidak ditemukan',
                'data' => [],
            ], 404);
        }
        $village->subdistrict_code = $subdistrict_code;
        $data['code'] = $data['code'] ?? $village->code;
        $data['name'] = $data['name'] ?? $village->name;
        $village->update($data);
        return $this->updatedJsonResponse(200, null, 'Desa berhasil diubah', new VillageResource($village));
    }

    public function deleteVillage($subdistrict_code, $id)
    {
        $subdistrict = Subdistrict::where('code', $subdistrict_code)->first();
        if (!$subdistrict) {
            return $this->errorJsonResponse(404, null, 'Kecamatan tidak ditemukan');
        }
        $subdistrict = Village::where([
            'id' => $id,
            'subdistrict_code' => $subdistrict->code,
        ])->first();
        $subdistrict->delete();

        return $this->successJsonResponse(200, null, 'Data desa berhasil dihapus', null);
    }
}
