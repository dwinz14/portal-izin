<?php

use Illuminate\Support\Facades\Auth;
use App\Models\Approval;
use App\Models\Leave;

if (!function_exists('getFilteredMenuItems')) {
    /**
     * Get menu items filtered by user role
     *
     * @return array
     */
    function getFilteredMenuItems()
    {
        $userRole = Auth::user()->role ?? null;
        $userId = Auth::id();

        if (!$userRole) {
            return [];
        }

        $menuConfig = config('menu.items', []);
        $filteredItems = [];

        // Calculate badge counts
        $badgeCounts = [];

        // Badge for "Approval Cuti" - count pending approvals for current user
        $badgeCounts['approval.index'] = Approval::with(['leave.approvals'])
            ->where('approver_id', $userId)
            ->where('status', 'pending')
            ->get()
            ->filter(function (Approval $approval) {
                $prev = $approval->leave->approvals->where('step', '<', $approval->step);
                return $prev->every(fn($x) => $x->status === 'approved');
            })
            ->count();

        // Badge for "Pengajuan Cuti" - count leaves with pending revisions for current user
        $badgeCounts['cuti.index'] = Leave::where('user_id', $userId)
            ->where('is_revision_pending', true)
            ->count();

        foreach ($menuConfig as $item) {
            // Check roles for parent menu
            if (in_array($userRole, $item['roles'])) {
                // Add badge count if exists
                if (isset($item['route']) && isset($badgeCounts[$item['route']])) {
                    $item['badge_count'] = $badgeCounts[$item['route']];
                }

                // If has children, filter children by role as well
                if (isset($item['children'])) {
                    $filteredChildren = [];
                    foreach ($item['children'] as $child) {
                        if (in_array($userRole, $child['roles'])) {
                            // Add badge count for children if exists
                            if (isset($child['route']) && isset($badgeCounts[$child['route']])) {
                                $child['badge_count'] = $badgeCounts[$child['route']];
                            }
                            $filteredChildren[] = $child;
                        }
                    }
                    if (!empty($filteredChildren)) {
                        $item['children'] = $filteredChildren;
                        $filteredItems[] = $item;
                    }
                } else {
                    $filteredItems[] = $item;
                }
            }
        }

        return $filteredItems;
    }
}

if (!function_exists('isMenuActive')) {
    /**
     * Check if a menu item is active based on route pattern
     *
     * @param string $pattern
     * @return bool
     */
    function isMenuActive($pattern)
    {
        return request()->routeIs($pattern);
    }
}
