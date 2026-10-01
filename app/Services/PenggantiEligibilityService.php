<?php

namespace App\Services;

use App\Models\Office;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Menentukan daftar user yang boleh jadi pengganti untuk seorang pemohon
 */
class PenggantiEligibilityService
{
    /**
     * @return Collection<int, User>
     */
    public function forRequester(User $requester): Collection
    {
        $query = User::query()->where('id', '!=', $requester->id);

        // Kantor yang tergabung dalam 1 grup
        $effectiveOfficeIds = Office::groupedOfficeIds($requester->office_id);
        $isGroupedOffice = count($effectiveOfficeIds) > 1;

        // Case 1: pemohon kabag-pincab di kantor yang tergabung grup -> semua user se-grup
        if ($requester->role === 'kabag-pincab' && $isGroupedOffice) {
            return $query->whereIn('office_id', $effectiveOfficeIds)
                ->orderBy('name')
                ->get();
        }

        // Case 2: pemohon kabag-pincab di kantor lain (bukan grup) -> pengganti kabag-pincab/hrd
        if ($requester->role === 'kabag-pincab') {
            return $query->whereIn('role', ['kabag-pincab', 'hrd'])
                ->orderBy('name')
                ->get();
        }

        // Case 3: role lain -> kantor pemohon sendiri, atau gabungan kalau masuk grup
        $requiresReplacement = in_array($requester->role, ['staff', 'kasie', 'kabag-pincab'], true);

        if (! $requiresReplacement) {
            return collect();
        }

        return $query->whereIn('office_id', $effectiveOfficeIds)
            ->orderBy('name')
            ->get();
    }
}
