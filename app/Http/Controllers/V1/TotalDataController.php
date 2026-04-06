<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\SubdistrictVisitor;
use App\Models\Visitor;

class TotalDataController extends Controller
{
    public function index()
    {
        $village_admin = Profile::with('user')
            ->whereHas('user', function ($query) {
                $query->where('level', 2);
            })
            ->distinct()
            ->count();
        $subdistrict_admin = Profile::with('user')
            ->whereHas('user', function ($query) {
                $query->where('level', 3);
            })
            ->distinct()
            ->count();
        $village_visitor = Visitor::count();
        $subdistrict_visitor = SubdistrictVisitor::count();
        return response()->json([
            "code" => 200,
            "status" => "success",
            "message" => "Berhasil menampilkan jumlah data",
            "data" => [
                "village_admin" => $village_admin,
                "subdistrict_admin" => $subdistrict_admin,
                "village_visitor" => $village_visitor,
                "subdistrict_visitor" => $subdistrict_visitor
            ]
        ]);
    }
}
