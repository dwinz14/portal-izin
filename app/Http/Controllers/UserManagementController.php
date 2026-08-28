<?php

namespace App\Http\Controllers;

use App\Models\QuotaSetting;
use App\Models\User;
use App\Services\LeaveQuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    public function index()
    {
        $threshold = now()->subMinutes(30)->timestamp;

        $maxActivitySub = DB::table('sessions')
            ->select('user_id', DB::raw('MAX(last_activity) as max_last_activity'))
            ->groupBy('user_id');

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
            ->whereNull('users.deleted_at')
            ->orderBy('is_online', 'desc')
            ->orderByRaw("
                CASE WHEN max_sessions.max_last_activity IS NOT NULL
                THEN max_sessions.max_last_activity
                ELSE users.last_login_at END DESC
            ")
            ->paginate(5);

        $onlineCount = DB::table('users')
            ->leftJoinSub($maxActivitySub, 'max_sessions', function ($join) {
                $join->on('users.id', '=', 'max_sessions.user_id');
            })
            ->where('max_sessions.max_last_activity', '>', $threshold)
            ->whereNull('users.deleted_at')
            ->count();

        $pendingUsers = User::with(['division', 'position', 'office'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        $pendingCount = $pendingUsers->count();

        // History: sertakan kolom baru (user_nik, verified_via)
        $approvalHistory = DB::table('user_registration_approvals')
            ->leftJoin('users', 'user_registration_approvals.approved_by', '=', 'users.id')
            ->select(
                'user_registration_approvals.user_name      as name',
                'user_registration_approvals.user_nik       as nik',
                'user_registration_approvals.user_email     as email',
                'user_registration_approvals.status',
                'user_registration_approvals.verified_via',
                'user_registration_approvals.updated_at',
                'users.name as approved_by_name'
            )
            ->orderBy('user_registration_approvals.updated_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.user-management.index', compact(
            'users',
            'onlineCount',
            'pendingUsers',
            'pendingCount',
            'approvalHistory'
        ));
    }

    public function approve($id)
    {
        $user = User::findOrFail($id);

        DB::transaction(function () use ($user) {
            $user->update(['status' => 'approved']);

            // Generate kuota cuti
            if (QuotaSetting::getValue('auto_generate_leave_balances', true)) {
                app(LeaveQuotaService::class)->generateForUser($user, now()->year);
            }

            DB::table('user_registration_approvals')->insert([
                'user_name'     => $user->name,
                'user_nik'      => $user->nik,
                'user_email'    => $user->email,
                'user_role'     => $user->role,
                'division_name' => $user->division?->nama_divisi,
                'approved_by'   => Auth::id(),
                'status'        => 'approved',
                'verified_via'  => 'admin',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        });

        return redirect()->back()->with('success', 'User berhasil disetujui dan kuota cuti telah digenerate.');
    }

    public function reject($id)
    {
        $user = User::findOrFail($id);

        DB::transaction(function () use ($user) {
            DB::table('user_registration_approvals')->insert([
                'user_name'     => $user->name,
                'user_nik'      => $user->nik,
                'user_email'    => $user->email,
                'user_role'     => $user->role,
                'division_name' => $user->division?->nama_divisi,
                'approved_by'   => Auth::id(),
                'status'        => 'rejected',
                'verified_via'  => 'admin',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            $user->delete();
        });

        return redirect()->back()->with('success', 'User berhasil ditolak.');
    }
}
