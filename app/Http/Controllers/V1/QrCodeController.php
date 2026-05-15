<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\QrCodeService;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    public function __construct(private QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    public function index()
    {
        return $this->qrCodeService->getQrCodeList();
    }

    public function show($village_code)
    {
        return $this->qrCodeService->getQrCodeDetail($village_code);
    }

    public function store(Request $request)
    {
        return $this->qrCodeService->storeQrCode($request);
    }

    public function storeSchoolQrCode(Request $request)
    {
        return $this->qrCodeService->storeSchoolQrCode($request);
    }

    public function update(Request $request, $id)
    {
        return $this->qrCodeService->updateQrCode($request, $id);
    }

    public function destroy($id)
    {
        return $this->qrCodeService->deleteQrCode($id);
    }
}
