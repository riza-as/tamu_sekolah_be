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
        $thisWeek = Carbon::now()->startOfWeek()->format('Y-m-d');
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;
        if ($user->level === 2) {
            $visitorTotal = Visitor::where('village_code', $user->profile->village_code)->count();
            $visitorToday = Visitor::where('village_code', $user->profile->village_code)->whereDate('created_at', $today)->count();
            $visitorWeek  = Visitor::where('village_code', $user->profile->village_code)->whereBetween('created_at', [$thisWeek, Carbon::now()->endOfWeek()])->count();
            $visitorMonth = Visitor::where('village_code', $user->profile->village_code)->whereMonth('created_at', $thisMonth)->count();
            $visitorYear  = Visitor::where('village_code', $user->profile->village_code)->whereYear('created_at', $thisYear)->count();
        } else {
            $visitorTotal = Visitor::count();
            $visitorToday = Visitor::whereDate('created_at', $today)->count();
            $visitorWeek  = Visitor::whereBetween('created_at', [$thisWeek, Carbon::now()->endOfWeek()])->count();
            $visitorMonth = Visitor::whereMonth('created_at', $thisMonth)->count();
            $visitorYear  = Visitor::whereYear('created_at', $thisYear)->count();
        }
        if ($user->level === 4) {
            $visitorTotal = Visitor::where('school_code', $user->profile->school_code)->count();
            $visitorToday = Visitor::where('school_code', $user->profile->school_code)
                ->whereDate('created_at', $today)
                ->count();
        }
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

        // ADMIN SEKOLAH
        if ($user->level === 4) {

            $total = DB::select("
            SELECT
                m.month_number AS month_number,
                m.month_name AS month_name,
                CASE
                    WHEN COALESCE(visitors.school_code, 0) = 0
                    THEN 0
                    ELSE COALESCE(COUNT(DISTINCT visitors.id), 0)
                END AS total_visitor
            FROM months m
            LEFT JOIN visitors
                ON CAST(m.month_number AS INTEGER) = EXTRACT(MONTH FROM visitors.created_at)
                AND EXTRACT(YEAR FROM visitors.created_at) = ?
                AND visitors.school_code = ?
            GROUP BY
                m.month_number,
                m.month_name,
                visitors.school_code
            ORDER BY
                CAST(m.month_number AS INTEGER)
        ", [
                $year,
                $user->profile->school_code
            ]);
        } else {

            // ADMIN DESA / SUPERADMIN (lama)
            $total = DB::select("
            SELECT
                m.month_number AS month_number,
                m.month_name AS month_name,
                CASE
                    WHEN COALESCE(profiles.village_code, 0) = 0
                    THEN 0
                    ELSE COALESCE(COUNT(DISTINCT v.id), 0)
                END AS total_visitor
            FROM months m
            LEFT JOIN visitors v
                ON CAST(m.month_number AS INTEGER) = EXTRACT(MONTH FROM v.created_at)
                AND EXTRACT(YEAR FROM v.created_at) = ?
            LEFT JOIN profiles
                ON v.village_code = profiles.village_code
                AND profiles.village_code = ?
            GROUP BY
                m.month_number,
                m.month_name,
                profiles.village_code
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
            'data' => !empty($total) ? $total : null,
        ], 200);
    }

    public function getVisitor()
    {
        $user = Auth::user();
        $page = max(1, (int) request()->get('page', 1));
        $limit = max(1, (int) request()->get('limit', 40));

        // Base query
        $query = Visitor::with(['school', 'school.level', 'school.status', 'village', 'visitorType', 'objective'])
            ->leftJoin('villages', 'villages.code', '=', 'visitors.village_code')
            ->leftJoin('visitor_types', 'visitor_types.id', '=', 'visitors.visitor_type_id')
            ->leftJoin('objectives', 'objectives.id', '=', 'visitors.objective_id')
            ->orderBy('visitors.created_at', 'desc');


        // Restrict level 2
        if ($user->level === 2) {
            $query->where('villages.code', $user->profile->village_code);
        }

        // Restrict level 4 (Sekolah)
        if ($user->level === 4) {
            $query->where('visitors.school_code', $user->profile->school_code);
        }

        // Filter by params
        if (request()->hasAny(['name','school_code', 'village_code', 'visitor_type_id', 'objective_id', 'created_at'])) {
            $query->when(request('name'), function ($q, $name) {
                $q->where('visitors.fullname', 'ilike', '%' . $name . '%');
            })->when(request('school_code'), function ($q, $schoolCode) {
                $q->where('visitors.school_code', $schoolCode);
            })->when(request('village_code'), function ($q, $villageCode) {
                $q->where('visitors.village_code', $villageCode);
            })->when(request('visitor_type_id'), function ($q, $visitorTypeId) {
                $q->where('visitor_types.id', $visitorTypeId);
            })->when(request('objective_id'), function ($q, $objectiveId) {
                $q->where('objectives.id', $objectiveId);
            })->when(request('created_at'), function ($q, $createdAt) {
                // Gunakan Carbon untuk filtering berdasarkan waktu
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
                    // Jika created_at bukan nilai yang dikenali, fallback ke like biasa
                    $q->where('visitors.created_at', 'like', '%' . $createdAt . '%');
                }
            });
        } else if ($user->level === 2) {
            $query->where('visitors.village_code', $user->profile->village_code);
        } else if ($user->level === 4) {
            $query->where('visitors.school_code', $user->profile->school_code);
        }

        // Paginate data
        $visitors = $query->paginate($limit);


        // Check if data is empty
        if ($visitors->isEmpty()) {
            return $this->errorListJsonResponse(404, null, 'Pengunjung tidak ditemukan');
        }

        // Response JSON
        return $this->listJsonResponse(200, null, 'Berhasil menampilkan data pengunjung', VisitorResource::collection($visitors));
    }

    // public function getVisitorSchool($school_code)
    // {
    //     $visitors = Visitor::where('school_code', $school_code)->get();

    //     if ($visitors->isEmpty()) {
    //         return $this->errorListJsonResponse(
    //             404,
    //             null,
    //             'Pengunjung tidak ditemukan'
    //         );
    //     }

    //     return $this->listJsonResponse(
    //         200,
    //         null,
    //         'Berhasil menampilkan data pengunjung sekolah',
    //         VisitorResource::collection($visitors)
    //     );
    // }

    public function showVisitor($id)
    {
        $visitor = Visitor::find($id);
        if (!$visitor) {
            return $this->errorJsonResponse(404, null, 'Pengunjung tidak ditemukan');
        }
        return $this->successJsonResponse(200, null, 'Berhasil menampilkan data pengunjung', new VisitorResource($visitor));
    }

    public function storeVisitor($request, $school_id,)
    {
        $school = School::where('school_code', $school_id)->first();
        // Cek jika ada file yang diunggah
        if ($request->hasFile('photo_visitor')) {

            // Proses upload gambar baru
            $photoVisitor = uniqid() . '.' . $request->photo_visitor->extension();
            $img = Image::make($request->photo_visitor->path());

            // Resize jika lebar gambar lebih dari 720px
            if ($img->width() > 720) {
                $img->resize(null, 720, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }

            // Sesuaikan orientasi gambar jika perlu
            $img->orientate();

            // Simpan gambar ke Storage disk 'public'
            $path = 'uploads/photo_visitors/' . $photoVisitor;
            Storage::disk('public')->put($path, (string) $img->encode());

            // Update path gambar pada profil
            $photoVisitorUrl = Storage::url($path);
            $request->photo_visitor = $photoVisitorUrl;
            $data_request['photo_visitor'] = $photoVisitorUrl;
        }
        $visitor = Visitor::create([
            'visitor_type_id' => $request->visitor_type_id,
            'village_code' => $school->village_code,
            'school_code' => $school->school_code,
            'fullname' => $request->fullname,
            'address' => $request->address,
            'photo_visitor' => url($request->photo_visitor),
            'objective_id' => $request->objective_id,
            'information' => $request->information
        ]);
        return $this->createdJsonResponse(201, null, 'Berhasil menambahkan data pengunjung', new VisitorResource($visitor));
    }

    public function destroyVisitor($id)
    {
        $visitor = Visitor::find($id);
        if (!$visitor) {
            return $this->errorJsonResponse(404, null, 'Pengunjung tidak ditemukan');
        }

        // Hapus gambar lama jika ada
        if ($visitor->photo_visitor) {
            $filename = basename($visitor->photo_visitor);
            Storage::disk('public')->delete('uploads/photo_visitors/' . $filename);
        }
        $visitor->delete();
        return $this->successJsonResponse(200, null, 'Berhasil menghapus data pengunjung');
    }
}
