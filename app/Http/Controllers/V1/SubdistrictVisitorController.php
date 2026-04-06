<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVisitorRequest;
use App\Services\SubdistrictVisitorService;

class SubdistrictVisitorController extends Controller
{
    public function __construct(private SubdistrictVisitorService $subdistrictVisitorService)
    {
        return $this->subdistrictVisitorService = $subdistrictVisitorService;
    }

    public function total()
    {
        return $this->subdistrictVisitorService->getSubdistrictVisitorTotal();
    }

    public function chart()
    {
        return $this->subdistrictVisitorService->chartSubdistrictVisitor();
    }

    public function index()
    {
        return $this->subdistrictVisitorService->getSubdistrictVisitors();
    }

    public function show($id)
    {
        return $this->subdistrictVisitorService->showSubdistrictVisitor($id);
    }

    public function store(StoreVisitorRequest $request, $subdistrict_code)
    {
        return $this->subdistrictVisitorService->storeSubdistrictVisitor($request, $subdistrict_code);
    }

    public function destroy($id)
    {
        return $this->subdistrictVisitorService->destroySubdistrictVisitor($id);
    }
}
