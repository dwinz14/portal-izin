<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Division;
use App\Models\Position;
use App\Models\Office;
use App\Models\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Services\ActivityLogger;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $annualType = LeaveType::where('name', 'cuti tahunan')->first();
        $annualBalance = null;

        if ($annualType) {
            $annualBalance = $request->user()->userLeaveBalances()
                ->where('leave_type_id', $annualType->id)
                ->where('year', now()->year)
                ->first();
        }

        return view('profile.edit', [
            'user' => $request->user()->load('division', 'position', 'office'),
            'divisions' => Division::all(),
            'positions' => Position::all(),
            'offices' => Office::all(),
            'annualType' => $annualType,
            'annualBalance' => $annualBalance,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->loadMissing(['position', 'division', 'office']);

        $before = [
            'position_id' => ['id' => $user->position_id, 'label' => $user->position->nama_jabatan ?? null],
            'division_id' => ['id' => $user->division_id, 'label' => $user->division->nama_divisi ?? null],
            'office_id'   => ['id' => $user->office_id,   'label' => $user->office->nama_kantor ?? null],
        ];

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        ActivityLogger::log('profile.updated', 'Memperbarui informasi profil');

        // Refresh relasi supaya label "sesudah" akurat (bukan cache sebelum fill()).
        $user->load(['position', 'division', 'office']);

        ActivityLogger::logRelationChanges($user, $before, [
            'position_id' => ['label' => 'jabatan', 'relation' => 'position', 'column' => 'nama_jabatan', 'event' => 'profile.position_changed'],
            'division_id' => ['label' => 'divisi',  'relation' => 'division', 'column' => 'nama_divisi',  'event' => 'profile.division_changed'],
            'office_id'   => ['label' => 'kantor',   'relation' => 'office',   'column' => 'nama_kantor',  'event' => 'profile.office_changed'],
        ]);

        return Redirect::route('profile.edit')->with('success', 'Profil berhasil diupdate.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
