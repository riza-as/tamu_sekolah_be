<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\VisitorTypeService;
use Illuminate\Http\Request;

class VisitorTypeController extends Controller
{
    public function __construct(private VisitorTypeService $visitorTypeService)
    {
        $this->visitorTypeService = $visitorTypeService;
    }

    public function index()
    {
        return $this->visitorTypeService->getVisitorType();
    }

    public function show($id)
    {
        return $this->visitorTypeService->getVisitorTypeById($id);
    }

    public function store(Request $request)
    {
        return $this->visitorTypeService->storeVisitorType($request);
    }

    public function update(Request $request, $id)
    {
        return $this->visitorTypeService->updateVisitorType($request, $id);
    }

    public function destroy($id)
    {
        return $this->visitorTypeService->destroyVisitorType($id);
    }
}
