<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LeaveResource;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Http\Request;

class LeaveApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Leave::with([
                'user' => function ($q) {
                    $q->select('id', 'name', 'nik', 'division_id', 'position_id', 'office_id');
                },
                'user.division' => function ($q) {
                    $q->select('id', 'nama_divisi');
                },
                'user.position' => function ($q) {
                    $q->select('id', 'nama_jabatan');
                },
                'user.office' => function ($q) {
                    $q->select('id', 'nama_kantor');
                },
                'leaveType' => function ($q) {
                    $q->select('id', 'name');
                }
            ]);

            // Filtering
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('leave_type_id')) {
                $query->where('leave_type_id', $request->leave_type_id);
            }

            if ($request->filled('status')) {
                $query->where('status_final', $request->status);
            }

            if ($request->filled(['start', 'end'])) {
                $query->whereBetween('start_date', [$request->start, $request->end]);
            }

            // Pagination (default 20, can override)
            $perPage = $request->get('per_page', 20);
            $result = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Leave list retrieved successfully',
                'data' => LeaveResource::collection($result),
                'meta' => [
                    'total'        => $result->total(),
                    'per_page'     => $result->perPage(),
                    'current_page' => $result->currentPage(),
                    'last_page'    => $result->lastPage(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve leave list',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Leave $leave)
    {
        return response()->json([
            'success' => true,
            'message' => 'Leave detail retrieved successfully',
            'data' => new LeaveResource($leave),
            'meta' => null
        ], 200);
    }


    public function byUser($userId, Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);

            $result = Leave::where('user_id', $userId)
                ->with(['user', 'leaveType'])
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Leave list for user retrieved successfully',
                'data' => LeaveResource::collection($result),
                'meta' => [
                    'total'        => $result->total(),
                    'per_page'     => $result->perPage(),
                    'current_page' => $result->currentPage(),
                    'last_page'    => $result->lastPage(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve leave for user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
