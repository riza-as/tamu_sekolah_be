<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\SubdistrictQrCodeService;
use Illuminate\Http\Request;

class SubdistrictQrCodeController extends Controller
{
  
    public function __construct(private SubdistrictQrCodeService $subdistrictCodeService) {}

    public function index()
    {
        return $this->subdistrictCodeService->getSubdistrictQrCodes();
    }

    public function show($subdistrict_code)
    {
        return $this->subdistrictCodeService->getSubdistrictQrCodeDetail($subdistrict_code);
    }

    public function store(Request $request)
    {
        return $this->subdistrictCodeService->storeSubdistrictQrCode($request);
    }

    public function update(Request $request, $id)
    {
        return $this->subdistrictCodeService->updateSubdistrictQrCode($request, $id);
    }

    public function destroy($id)
    {
        return $this->subdistrictCodeService->deleteSubdistrictQrCode($id);
    }
}