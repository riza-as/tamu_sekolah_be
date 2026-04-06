<?php

namespace App\Services;

use App\Http\Resources\ProvinceResource;
use App\Models\Province;

class ProvinceService extends ResponseService
{

    public function getProvince()
    {
        $limit = request()->get('limit', 40);
        $page = request()->get('page');
        $provinces = Province::orderBy('code', 'asc')->paginate($limit, ['*'], 'page', $page);
        if (request()->has('name')) {
            $name = request()->name;
            $provinces = Province::where('name', 'like', '%' . $name . '%')->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);
            if ($provinces->isEmpty()) {
                return  $this->errorListJsonResponse(404, 'Provinsi tidak ditemukan');
            }
            return $this->listJsonResponse(200, null, 'Berhasil menemukan provinsi', ProvinceResource::collection($provinces));
        }
        return $this->listJsonResponse(200, null, 'Data provinsi berhasil ditampilkan', ProvinceResource::collection($provinces));
    }

    public function storeProvince($request)
    {
        $request->validate([
            'code' => 'required|unique:provinces,code',
            'name' => 'required|unique:provinces,name',
        ], [
            'code.required' => 'Kode provinsi harus diisi',
            'name.required' => 'Nama provinsi harus diisi',
        ]);

        $province = new Province();
        $province->code = $request->code;
        $province->name = $request->name;
        $province->save();
        return $this->createdJsonResponse(201, null, 'Provinsi berhasil ditambahkan', new ProvinceResource($province));
    }

    public function updateProvince($request, $id)
    {
        $request->validate([
            'code' => 'nullable',
            'name' => 'nullable',
        ]);

        $province = Province::find($id);
        $province->code = $request->code;
        $province->name = $request->name;
        $province->save();
        return $this->updatedJsonResponse(200, null, 'Provinsi berhasil diperbarui', new ProvinceResource($province));
    }

    public function deleteProvince($id)
    {
        $province = Province::find($id);
        $province->delete();
        return $this->successJsonResponse(200, null, 'Provinsi berhasil dihapus', null);
    }
}
