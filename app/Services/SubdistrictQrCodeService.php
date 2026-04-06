<?php

namespace App\Services;

use App\Http\Resources\SubdistrictQrCodeResource;
use App\Models\SubdistrictQrCode;

class SubdistrictQrCodeService extends ResponseService
{
    public function getSubdistrictQrCodes()
    {
        $page = max(1, (int) request()->get('page', 1));
        $limit = max(1, (int) request()->get('limit', 40));

        // Use eager loading for query data
        $query = SubdistrictQrCode::with('subdistricts')
            ->join('subdistricts', 'subdistrict_qr_codes.subdistrict_code', '=', 'subdistricts.code')
            ->distinct()
            ->orderBy('subdistrict_qr_codes.created_at', 'desc');

        // Filter by params
        if (request()->hasAny(['name'])) {
            $query->when(request('name'), function ($q, $name) {
                $q->where('subdistricts.name', 'like', '%' . $name . '%');
            });
        }

        // Paginate data
        $subdistrict_qr_codes = $query->paginate($limit, ['subdistrict_qr_codes.*'], 'page', $page);

        // Check if data is empty
        if ($subdistrict_qr_codes->isEmpty()) {
            return $this->errorListJsonResponse(404, null, 'Kode QR Kecamatan tidak ditemukan');
        }

        // Response JSON
        return $this->listJsonResponse(200, null, 'Berhasil menampilkan data kode QR Kecamatan', SubdistrictQrCodeResource::collection($subdistrict_qr_codes));
    }

    public function getSubdistrictQrCodeDetail($subdistrict_code)
    {
        $subdistrict_qr_code = SubdistrictQrCode::where('subdistrict_code', $subdistrict_code)->first();
        if (!$subdistrict_qr_code) {
            return $this->errorJsonResponse(404, null, 'Kode QR Kecamatan tidak ditemukan');
        }
        return $this->successJsonResponse(200, null, 'Berhasil menampilkan data kode QR Kecamatan', new SubdistrictQrCodeResource($subdistrict_qr_code));
    }

    public function storeSubdistrictQrCode($request)
    {
        $subdistrict_qr_code = SubdistrictQrCode::where('subdistrict_code', $request->subdistrict_code)->first();
        if ($subdistrict_qr_code) {
            return $this->errorJsonResponse(400, null, 'Kode QR Kecamatan sudah ada');
        }
        $subdistrict_qr_code = SubdistrictQrCode::create([
            'subdistrict_code' => $request->subdistrict_code,
            'link_qr_code' => "https://tamudesa.id/form/subdistrict/$request->subdistrict_code",
            'status'       => 1
        ]);
        return $this->createdJsonResponse(201, null, 'Berhasil menambahkan data kode QR Kecamatan', new SubdistrictQrCodeResource($subdistrict_qr_code));
    }

    public function updateSubdistrictQrCode($request, $id)
    {
        $subdistrict_qr_code = SubdistrictQrCode::where('id', $id)->first();
        if (!$subdistrict_qr_code) {
            return $this->errorJsonResponse(404, null, 'Kode QR Kecamatan tidak ditemukan');
        }
        $subdistrict_qr_code->update([
            'status'       => $request->status
        ]);
        return $this->updatedJsonResponse(200, null, 'Berhasil mengubah data kode QR Kecamatan', new SubdistrictQrCodeResource($subdistrict_qr_code));
    }

    public function deleteSubdistrictQrCode($id)
    {
        $subdistrict_qr_code = SubdistrictQrCode::where('id', $id)->first();
        if (!$subdistrict_qr_code) {
            return $this->errorJsonResponse(404, null, 'Kode QR Kecamatan tidak ditemukan');
        }
        $subdistrict_qr_code->delete();
        return $this->successJsonResponse(200, null, 'Berhasil menghapus data kode QR Kecamatan');
    }
}
