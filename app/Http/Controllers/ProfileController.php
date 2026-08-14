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
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('success', 'profile berhasil diupdate');
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
