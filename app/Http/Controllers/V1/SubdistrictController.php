<?php

namespace App\Http\Controllers\V1;

use App\Services\SubdistrictService;
use Illuminate\Http\Request;

class SubdistrictController
{
    public function __construct(private SubdistrictService $subdistrictService)
    {
        $this->subdistrictService = $subdistrictService;
    }
    public function index()
    {
        return $this->subdistrictService->getSubdistrict();
    }

    public function store(Request $request, $district_code)
    {
        return $this->subdistrictService->storeSubdistrict($request, $district_code);
    }
    public function update(Request $request, $district_code, $id)
    {
        return $this->subdistrictService->updateSubdistrict($request, $district_code, $id);
    }

    public function delete($district_code, $id)
    {
        return $this->subdistrictService->deleteSubdistrict($district_code, $id);
    }
}
