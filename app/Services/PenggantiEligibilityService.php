<?php

namespace App\Services;

use App\Models\Office;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Menentukan daftar user yang boleh jadi pengganti
 */
class PenggantiEligibilityService
{
    /**
     * @return Collection<int, User>
     */
    public function forRequester(User $requester): Collection
    {
        $query = User::query()->where('id', '!=', $requester->id);

        // Case 1: pemohon kabag-pincab & kantor pusat -> pengganti dari kantor pusat
        if ($requester->role === 'kabag-pincab' && $requester->office_id == Office::PUSAT) {
            return $query->where('office_id', Office::PUSAT)
                ->orderBy('name')
                ->get();
        }

        // Case 2: pemohon kabag-pincab tapi bukan kantor pusat -> pengganti kabag-pincab/hrd
        if ($requester->role === 'kabag-pincab' && $requester->office_id != Office::PUSAT) {
            return $query->whereIn('role', ['kabag-pincab', 'hrd'])
                ->orderBy('name')
                ->get();
        }

        // Case 3: role lain -> tetap satu kantor dengan pemohon
        $requiresReplacement = in_array($requester->role, ['staff', 'kasie', 'kabag-pincab'], true);

        if (! $requiresReplacement) {
            return collect();
        }

        return $query->where('office_id', $requester->office_id)
            ->orderBy('name')
            ->get();
    }
}
