<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVisitorRequest;
use App\Services\VisitorService;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    public function __construct(private VisitorService $visitorService)
    {
        $this->visitorService = $visitorService;
    }

    public function total()
    {
        return $this->visitorService->getVisitorTotal();
    }

    public function chart()
    {
        return $this->visitorService->chartVisitor();
    }
    public function index()
    {
        return $this->visitorService->getVisitor();
    }

    public function show($id)
    {
        return $this->visitorService->showVisitor($id);
    }

    public function store(StoreVisitorRequest $request, $school_id)
    {
        return $this->visitorService->storeVisitor($request, $school_id);
    }

    public function destroy($id)
    {
        return $this->visitorService->destroyVisitor($id);
    }
}
