<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class UserActivityController extends Controller
{
    private const ONLINE_MINUTES = 30;

    // ── Index: Feed global semua user ─────────────────────────────────────

    public function index(Request $request)
    {
        $threshold = now()->subMinutes(self::ONLINE_MINUTES)->timestamp;

        $stats = $this->globalStats($threshold);

        $query = Activity::with(['causer'])
            ->where('log_name', 'user_activity')
            ->orderByDesc('created_at');

        // Filter: cari nama atau NIK
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHasMorph('causer', [User::class], function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // Filter: kategori event
        if ($request->filled('category')) {
            $query->where('properties->category', $request->category);
        }

        // Filter: rentang tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->paginate(30)->withQueryString();

        return view('admin.user-activity.index', compact('activities', 'stats'));
    }

    // ── Show: Detail timeline per user ────────────────────────────────────

    public function show(User $user, Request $request)
    {
        $threshold = now()->subMinutes(self::ONLINE_MINUTES)->timestamp;

        $isOnline = DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('last_activity', '>', $threshold)
            ->exists();

        $stats = $this->userStats($user);

        $query = Activity::where('log_name', 'user_activity')
            ->where('causer_id', $user->id)
            ->where('causer_type', User::class)
            ->orderByDesc('created_at');

        if ($request->filled('category')) {
            $query->where('properties->category', $request->category);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->paginate(30)->withQueryString();

        // Kelompokkan per hari untuk tampilan timeline
        $grouped = $activities->getCollection()->groupBy(fn($a) => $a->created_at->format('Y-m-d'));

        return view('admin.user-activity.show', compact(
            'user',
            'isOnline',
            'stats',
            'activities',
            'grouped'
        ));
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private function globalStats(int $threshold): array
    {
        return [
            'activities_today' => Activity::where('log_name', 'user_activity')
                ->whereDate('created_at', today())->count(),

            'online_count' => DB::table('sessions')
                ->where('last_activity', '>', $threshold)
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id'),

            'logins_today' => Activity::where('log_name', 'user_activity')
                ->whereDate('created_at', today())
                ->where('properties->event_type', 'auth.login')
                ->count(),

            'activities_7days' => Activity::where('log_name', 'user_activity')
                ->where('created_at', '>=', now()->subDays(7))->count(),
        ];
    }

    private function userStats(User $user): array
    {
        $base = Activity::where('log_name', 'user_activity')
            ->where('causer_id', $user->id)
            ->where('causer_type', User::class);

        $thisMonth = fn() => (clone $base)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);

        return [
            'total'           => (clone $base)->count(),
            'logins_month'    => (clone $thisMonth())->where('properties->event_type', 'auth.login')->count(),
            'leaves_month'    => (clone $thisMonth())->where('properties->event_type', 'leave.submitted')->count(),
            'approvals_month' => (clone $thisMonth())->where(function ($q) {
                $q->where('properties->event_type', 'approval.leave_approved')
                    ->orWhere('properties->event_type', 'approval.leave_rejected')
                    ->orWhere('properties->event_type', 'approval.attendance_approved')
                    ->orWhere('properties->event_type', 'approval.attendance_rejected');
            })->count(),
            'last_login_at'   => $user->last_login_at,
        ];
    }
}
