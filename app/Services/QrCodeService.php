<?php

namespace App\Services;

use App\Http\Resources\QrCodeResource;
use App\Models\QrCode;
use Illuminate\Support\Facades\Auth;

class QrCodeService extends ResponseService
{
    public function getQrCodeList()
    {
        $page = max(1, (int) request()->get('page', 1));
        $limit = max(1, (int) request()->get('limit', 40));

        // Use eager loading for query data
        $query = QrCode::orderBy('qr_codes.created_at', 'desc');

        // Filter by params
        if (request()->hasAny(['village_code', 'name'])) {
            $query->when(request('village_code'), function ($q, $village_code) {
                $q->where('qr_codes.village_code', $village_code);
            })
                ->when(request('name'), function ($q, $name) {
                    $q->where('villages.name', 'like', '%' . $name . '%');
                });
        }

        //Filter For qr code school
        if (request()->hasAny(['school_code', 'name'])) {
            $query->when(request('school_code'), function ($q, $school_code) {
                $q->where('qr_codes.school_code', $school_code);
            })
                ->when(request('name'), function ($q, $name) {
                    $q->where('schools.name', 'like', '%' . $name . '%');
                });
        }

        // Paginate data
        $qr_codes = $query->paginate($limit, ['qr_codes.*'], 'page', $page);

        // Check if data is empty
        if ($qr_codes->isEmpty()) {
            return $this->errorListJsonResponse(404, null, 'Kode QR tidak ditemukan');
        }

        // Response JSON
        return $this->listJsonResponse(200, null, 'Berhasil menampilkan data kode QR', QrCodeResource::collection($qr_codes));
    }

    public function getQrCodeDetail($village_code)
    {
        $qr_code = QrCode::where('village_code', $village_code)->first();
        if (!$qr_code) {
            return $this->errorJsonResponse(404, null, 'Kode QR tidak ditemukan');
        }
        return $this->successJsonResponse(200, null, 'Berhasil menampilkan data kode QR', new QrCodeResource($qr_code));
    }

    public function storeQrCode($request)
    {
        $qr_code = QrCode::create([
            'village_code' => $request->village_code,
            'link_qr_code' => "https://tamudesa.id/form/village/$request->village_code",
            'status'       => 1
        ]);
        return $this->createdJsonResponse(201, null, 'Berhasil menambahkan data kode QR', new QrCodeResource($qr_code));
    }

    public function storeSchoolQrCode($request)
    {
        $qr_code = QrCode::create([
            'school_code' => $request->school_code,
            'link_qr_code' => "https://tamusekolah.id/form/school/$request->school_code",
            'status' => 1
        ]);

        return $this->createdJsonResponse(
            201,
            null,
            'Berhasil membuat QR sekolah',
            new QrCodeResource($qr_code)
        );

    }

    public function updateQrCode($request, $id)
    {
        $qr_code = QrCode::find($id);
        if ($qr_code === null) {
            return $this->errorJsonResponse(404, null, 'Kode QR tidak ditemukan');
        }
        $qr_code->update([
            'status'       => $request->status
        ]);
        return $this->updatedJsonResponse(200, null, 'Berhasil mengubah data kode QR', new QrCodeResource($qr_code));
    }

    public function deleteQrCode($id)
    {
        $qr_code = QrCode::find($id);
        if ($qr_code === null) {
            return $this->errorJsonResponse(404, null, 'Kode QR tidak ditemukan');
        }
        $qr_code->delete();
        return $this->successJsonResponse(200, null, 'Berhasil menghapus data kode QR');
    }
}
