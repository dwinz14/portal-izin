<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserManagementController extends Controller
{
    public function index()
    {
        $threshold = now()->subMinutes(30)->timestamp;

        // Subquery untuk ambil last_activity terakhir tiap user
        $maxActivitySub = DB::table('sessions')
            ->select('user_id', DB::raw('MAX(last_activity) as max_last_activity'))
            ->groupBy('user_id');

        // Query utama
        $users = DB::table('users')
            ->leftJoin('divisions', 'users.division_id', '=', 'divisions.id')
            ->leftJoinSub($maxActivitySub, 'max_sessions', function ($join) {
                $join->on('users.id', '=', 'max_sessions.user_id');
            })
            ->selectRaw("
                users.id,
                users.name,
                users.email,
                users.role,
                users.status,
                users.last_login_at,
                divisions.nama_divisi as division_name,
                CASE WHEN max_sessions.max_last_activity > ? THEN 1 ELSE 0 END as is_online
            ", [$threshold])
            ->orderBy('is_online', 'desc')
            ->orderByRaw("
                CASE WHEN
                    max_sessions.max_last_activity IS NOT NULL
                THEN max_sessions.max_last_activity
                ELSE users.last_login_at
                END DESC
            ")
            ->paginate(5);

        // Count online users separately without pagination
        $onlineCount = DB::table('users')
            ->leftJoinSub($maxActivitySub, 'max_sessions', function ($join) {
                $join->on('users.id', '=', 'max_sessions.user_id');
            })
            ->where('max_sessions.max_last_activity', '>', $threshold)
            ->count();

        // Get pending users for approval
        $pendingUsers = User::with('division')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        $pendingCount = $pendingUsers->count();

        // Get approval history from user_registration_approvals
        $approvalHistory = DB::table('user_registration_approvals')
            ->leftJoin('users', 'user_registration_approvals.approved_by', '=', 'users.id')
            ->select(
                'user_registration_approvals.user_name as name',
                'user_registration_approvals.user_email as email',
                'user_registration_approvals.status',
                'user_registration_approvals.updated_at',
                'users.name as approved_by'
            )
            ->orderBy('user_registration_approvals.updated_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.user-management.index', compact('users', 'onlineCount', 'pendingUsers', 'pendingCount', 'approvalHistory'));
    }

    public function approve($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'approved']);

        // Save approval history
        DB::table('user_registration_approvals')->insert([
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'division_name' => $user->division->nama_divisi ?? null,
            'approved_by' => Auth::id(),
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'User berhasil disetujui.');
    }

    public function reject($id)
    {
        $user = User::findOrFail($id);

        // Save approval history before deleting user
        DB::table('user_registration_approvals')->insert([
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'division_name' => $user->division->nama_divisi ?? null,
            'approved_by' => Auth::id(),
            'status' => 'rejected',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Delete user from users table
        $user->delete();

        return redirect()->back()->with('success', 'User berhasil ditolak dan dihapus.');
    }
}
