<?php

namespace App\Services;

use App\Http\Resources\VisitorResource;
use App\Models\Visitor;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class VisitorService extends ResponseService
{

    public function getVisitorTotal()
    {
        $user = Auth::user();
        $today = Carbon::now()->format('Y-m-d');
        $thisWeek = [Carbon::now()->startOfWeek()->format('Y-m-d'), Carbon::now()->endOfWeek()->format('Y-m-d')];
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        $query = Visitor::query();

        if ($user->level === 2) {
            $query->whereHas('school', function ($q) use ($user) {
                $q->where('subdistrict_code', $user->profile->subdistrict_code);
            });
        } elseif ($user->level === 4) {

            $query->where('school_code', $user->profile->school_code);
        }

        $visitorTotal = (clone $query)->count();

        $visitorToday = (clone $query)->whereDate('created_at', $today)->count();

        $visitorWeek  = (clone $query)->whereBetween('created_at', $thisWeek)->count();

        $visitorMonth = (clone $query)->whereMonth('created_at', $thisMonth)->count();

        $visitorYear  = (clone $query)->whereYear('created_at', $thisYear)->count();

        return response()->json([
            "code" => 200,
            "status" => "success",
            "message" => "Berhasil menampilkan jumlah pengunjung",
            "data" => [
                "total_visitor" => $visitorTotal,
                "visitor_today" => $visitorToday,
                "visitor_week"  => $visitorWeek,
                "visitor_month" => $visitorMonth,
                "visitor_year"  => $visitorYear
            ]
        ]);
    }

    public function chartVisitor()
    {
        $user = Auth::user();
        $year = request()->get('year') ?? now()->year;

        if ($user->level === 4) {

            $total = DB::select("
            SELECT
                m.month_number AS month_number,
                m.month_name AS month_name,
                COALESCE(COUNT(v.id), 0) AS total_visitor
            FROM months m
            LEFT JOIN visitors v
                ON CAST(m.month_number AS INTEGER) = EXTRACT(MONTH FROM v.created_at)
                AND EXTRACT(YEAR FROM v.created_at) = ?
                AND v.school_code = ?
            GROUP BY
                m.month_number,
                m.month_name
            ORDER BY
                CAST(m.month_number AS INTEGER)
        ", [
                $year,
                $user->profile->school_code
            ]);
        } else {
          
            $total = DB::select("
            SELECT
                m.month_number AS month_number,
                m.month_name AS month_name,
                COALESCE(COUNT(v.id), 0) AS total_visitor
            FROM months m
            LEFT JOIN visitors v
                ON CAST(m.month_number AS INTEGER) = EXTRACT(MONTH FROM v.created_at)
                AND EXTRACT(YEAR FROM v.created_at) = ?
            LEFT JOIN profiles p
                ON v.village_code = p.village_code
                AND p.village_code = ?
            GROUP BY
                m.month_number,
                m.month_name
            ORDER BY
                CAST(m.month_number AS INTEGER)
        ", [
                $year,
                $user->profile->village_code
            ]);
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => "Diagram pengunjung berhasil ditampilkan",
            'data' => $total,
        ], 200);
    }

    public function getVisitor()
    {
        $user = Auth::user();
        $page = max(1, (int) request()->get('page', 1));
        $limit = max(1, (int) request()->get('limit', 40));

        $query = Visitor::with(['school.level', 'school.status', 'visitorType', 'objective'])
            ->leftJoin('objectives', 'objectives.id', '=', 'visitors.objective_id')
            ->orderBy('visitors.created_at', 'desc')
            ->select('visitors.*');

        if ($user->level === 4) {
            $query->where('visitors.school_code', $user->profile->school_code);
        }

        if (request()->hasAny(['name', 'school_code', 'visitor_type_id', 'objective_id', 'created_at'])) {
            $query->when(request('name'), function ($q, $name) {
                $q->where('visitors.fullname', 'ilike', '%' . $name . '%');
            })->when(request('school_code'), function ($q, $schoolCode) {
                $q->where('visitors.school_code', $schoolCode);
            })->when(request('visitor_type_id'), function ($q, $visitorTypeId) {
                $q->where('visitors.visitor_type_id', $visitorTypeId);
            })->when(request('objective_id'), function ($q, $objectiveId) {
                $q->where('objectives.id', $objectiveId);
            })->when(request('created_at'), function ($q, $createdAt) {
                if ($createdAt === 'today') {
                    $q->whereDate('visitors.created_at', Carbon::today());
                } elseif ($createdAt === 'this_week') {
                    $q->whereBetween('visitors.created_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                } elseif ($createdAt === 'this_month') {
                    $q->whereMonth('visitors.created_at', Carbon::now()->month);
                } elseif ($createdAt === 'this_year') {
                    $q->whereYear('visitors.created_at', Carbon::now()->year);
                } else {
                    $q->where('visitors.created_at', 'like', '%' . $createdAt . '%');
                }
            });
        }

        $visitors = $query->paginate($limit);

        if ($visitors->isEmpty()) {
            return $this->errorListJsonResponse(200, null, 'Pengunjung tidak ditemukan');
        }

        return $this->listJsonResponse(200, null, 'Berhasil menampilkan data pengunjung', VisitorResource::collection($visitors));
    }

    public function showVisitor($id)
    {
        $visitor = Visitor::find($id);
        if (!$visitor) {
            return $this->errorJsonResponse(404, null, 'Pengunjung tidak ditemukan');
        }
        return $this->successJsonResponse(200, null, 'Berhasil menampilkan data pengunjung', new VisitorResource($visitor));
    }

    public function storeVisitor($request, $school_id)
    {
        $npsn = ($school_id && $school_id !== 'undefined') ? $school_id : $request->school_code;

        $school = School::where('school_code', $npsn)->first();

        if (!$school) {
            return response()->json([
                "code" => 404,
                "status" => "error",
                "message" => "Sekolah dengan kode {$npsn} tidak ditemukan."
            ], 404);
        }

        if ($request->hasFile('photo_visitor')) {
            $photoVisitor = uniqid() . '.' . $request->photo_visitor->extension();
            $img = Image::make($request->photo_visitor->path());

            if ($img->width() > 720) {
                $img->resize(null, 720, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }

            $img->orientate();
            $path = 'uploads/photo_visitors/' . $photoVisitor;
            Storage::disk('public')->put($path, (string) $img->encode());

            $photoVisitorUrl = Storage::url($path);
            $request->photo_visitor = $photoVisitorUrl;
        }

        $visitor = Visitor::create([
            'visitor_type_id' => $request->visitor_type_id,
            'school_code'     => $school->school_code,
            'fullname'        => $request->fullname,
            'address'         => $request->address,
            'photo_visitor'   => url($request->photo_visitor),
            'objective_id'    => $request->objective_id,
            'information'     => $request->information
        ]);

        return $this->createdJsonResponse(201, null, 'Berhasil menambahkan data pengunjung', new VisitorResource($visitor));
    }

    public function destroyVisitor($id)
    {
        $visitor = Visitor::find($id);
        if (!$visitor) {
            return $this->errorJsonResponse(404, null, 'Pengunjung tidak ditemukan');
        }

       
        if ($visitor->photo_visitor) {
            $filename = basename($visitor->photo_visitor);
            Storage::disk('public')->delete('uploads/photo_visitors/' . $filename);
        }
        $visitor->delete();
        return $this->successJsonResponse(200, null, 'Berhasil menghapus data pengunjung');
    }
}
