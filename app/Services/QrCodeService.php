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


        $query = QrCode::with('school')->orderBy('qr_codes.created_at', 'desc');


        if (request()->hasAny(['subdistrict_code', 'name'])) {
            $query->when(request('subdistrict_code'), function ($q, $subdistrict_code) {
                $q->where('qr_codes.subdistrict_code', $subdistrict_code);
            });
        }


        if (request()->hasAny(['school_code', 'name'])) {
            $query->when(request('school_code'), function ($q, $school_code) {
                $q->where('qr_codes.school_code', $school_code);
            })
                ->when(request('name'), function ($q, $name) {
                    $q->whereHas('school', function ($queryJoin) use ($name) {
                        $queryJoin->where('name', 'like', '%' . $name . '%');
                    });
                });
        }

        $qr_codes = $query->paginate($limit, ['qr_codes.*'], 'page', $page);

        if ($qr_codes->isEmpty()) {
            return $this->errorListJsonResponse(404, null, 'Kode QR tidak ditemukan');
        }

        return $this->listJsonResponse(200, null, 'Berhasil menampilkan data kode QR', QrCodeResource::collection($qr_codes));
    }


    public function getQrCodeDetail($school_code)
    {
        $qr_code = QrCode::with('school')->where('school_code', $school_code)->first();

        if (!$qr_code) {
            return $this->errorJsonResponse(404, null, 'Kode QR tidak ditemukan');
        }

        return $this->successJsonResponse(200, null, 'Berhasil menampilkan data kode QR', new QrCodeResource($qr_code));
    }

    public function storeQrCode($request)
    {
        $request->validate([
            'school_code' => 'required|string',
        ]);

        $schoolCode = $request->school_code;

        $qr_code = QrCode::create([
            //href link qr code, ubah aja kalau mau di deploy
        'school_code' => $schoolCode,
            // 'link_qr_code' => "https://tamusekolah.id/form/school/$schoolCode",
            // 'link_qr_code' => "https://tamudesa.id/form/school/$schoolCode",
            'link_qr_code' => "http://localhost:3000/form/school/$schoolCode",
            'status'       => 1
        ]);


        $qr_code->load('school');

        return $this->createdJsonResponse(201, null, 'Berhasil menambahkan data kode QR Sekolah', new QrCodeResource($qr_code));
    }

    public function updateQrCode($request, $id)
    {
        $qr_code = QrCode::find($id);
        if ($qr_code === null) {
            return $this->errorJsonResponse(404, null, 'Kode QR tidak ditemukan');
        }

        $qr_code->update([
            'status' => $request->status
        ]);

        return $this->updatedJsonResponse(200, null, 'Berhasil mengubah data kode QR', new QrCodeResource($qr_code->load('school')));
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
