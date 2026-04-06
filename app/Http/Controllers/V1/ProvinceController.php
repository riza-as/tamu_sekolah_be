<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\ProvinceService;
use Illuminate\Http\Request;

class ProvinceController extends Controller
{
    public function __construct(private ProvinceService $provinceService)
    {
        return $this->provinceService = $provinceService;
    }

    public function index()
    {
        return $this->provinceService->getProvince();
    }

    public function store(Request $request)
    {
        return $this->provinceService->storeProvince($request);
    }

    public function update(Request $request, $id)
    {
        return $this->provinceService->updateProvince($request, $id);
    }

    public function delete($id)
    {
        return $this->provinceService->deleteProvince($id);
    }
}
