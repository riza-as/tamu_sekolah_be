<?php

namespace App\Http\Controllers\V1;

use App\Services\DistrictService;
use Illuminate\Http\Request;

class DistrictController
{
    public function __construct(private DistrictService $districtService)
    {
        $this->districtService = $districtService;
    }
    public function index()
    {
        return $this->districtService->getDistrict();
    }

    public function store(Request $request, $province_code)
    {
        return $this->districtService->storeDistrict($request, $province_code);
    }
    public function update(Request $request, $province_code, $id)
    {
        return $this->districtService->updateDistrict($request, $province_code, $id);
    }

    public function delete($province_code, $id)
    {
        return $this->districtService->deleteDistrict($province_code, $id);
    }
}
