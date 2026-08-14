<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Division;
use App\Models\Position;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MasterUserController extends Controller
{
    public function index(Request $request)
    {
        $search     = $request->get('search');
        $role       = $request->get('role');
        $divisionId = $request->get('division_id');
        $positionId = $request->get('position_id');
        $officeId   = $request->get('office_id');

        $users = User::with(['division', 'position', 'office'])
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($role, fn($q) => $q->where('role', $role))
            ->when($divisionId, fn($q) => $q->where('division_id', $divisionId))
            ->when($positionId, fn($q) => $q->where('position_id', $positionId))
            ->when($officeId, fn($q) => $q->where('office_id', $officeId))
            ->orderBy('name')
            ->paginate(8)
            ->withQueryString();

        $divisions = Cache::remember('divisions_all', 3600, fn() => Division::all());
        $positions = Cache::remember('positions_all', 3600, fn() => Position::all());
        $offices = Cache::remember('offices_all', 3600, fn() => Office::all());

        // // Convert user names to title case for display
        // $users->getCollection()->transform(function ($user) {
        //     $user->name = ucwords(strtolower($user->name));
        //     return $user;
        // });
        // $divisions->getCollection()->transform(function ($division) {
        //     $division->nama_divisi = ucwords(strtolower($division->nama_divisi));
        //     return $divisions;
        // });

        return view('admin.users.index', compact('users', 'divisions', 'positions', 'offices', 'search', 'role', 'divisionId', 'positionId', 'officeId'));
    }

    public function create()
    {
        $divisions = Cache::remember('divisions_all', 3600, fn() => Division::all());
        $positions = Cache::remember('positions_all', 3600, fn() => Position::all());
        $offices = Cache::remember('offices_all', 3600, fn() => Office::all());
        return view('admin.users.create', compact('divisions', 'positions', 'offices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik'        => [
                'required',
                'string',
                'size:11',
                'regex:/^[A-Z]{2}[0-9]{9}$/',
                'unique:users,nik',
            ],
            'name'        => ['bail', 'required', 'string', 'max:255', 'regex:/^[a-zA-Z\\s]+$/'],
            'email'       => ['required', 'email', 'unique:users,email'],
            'gender'      => ['nullable', 'in:L,P'],
            'tanggal_aktif_kerja' => ['nullable', 'date', 'before_or_equal:today'],
            'role'        => ['required', 'in:super_admin,hrd,kabag-pincab,kasie,staff,direksi'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'office_id'   => ['nullable', 'exists:offices,id'],

            // Pesan error kustom (opsional)
            'nik.regex' => 'Format NIK salah.',
            'tanggal_aktif_kerja.before_or_equal' => 'Tanggal aktif kerja tidak boleh lebih dari hari ini.',
        ]);


        // Normalisasi & sanitasi data
        $validated['name'] = strtolower(strip_tags(trim($validated['name'])));
        $validated['email'] = strtolower(trim($validated['email']));

        // Gunakan default password dari .env
        $defaultPassword = config('app.default_user_password', 'password123');
        $validated['password'] = Hash::make($defaultPassword);
        $validated['must_change_password'] = true;

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan dengan password default.');
    }

    public function edit(User $user)
    {
        $divisions = Cache::remember('divisions_all', 3600, fn() => Division::all());
        $positions = Cache::remember('positions_all', 3600, fn() => Position::all());
        $offices = Cache::remember('offices_all', 3600, fn() => Office::all());
        return view('admin.users.edit', compact('user', 'divisions', 'positions', 'offices'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nik'        => [
                'required',
                'string',
                'size:11',
                'regex:/^[A-Z]{2}[0-9]{9}$/',
                'unique:users,nik,' . $user->id,
            ],
            'name'        => ['bail', 'required', 'string', 'max:255', 'regex:/^[a-zA-Z\\s]+$/'],
            'email'       => ['required', 'email', 'unique:users,email,' . $user->id],
            'gender'      => ['nullable', 'in:L,P'],
            'tanggal_aktif_kerja' => ['nullable', 'date', 'before_or_equal:today'],
            'role'        => ['required', 'in:super_admin,hrd,kabag-pincab,kasie,staff,direksi'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'office_id'   => ['nullable', 'exists:offices,id'],

            // Pesan error kustom (opsional)
            'nik.regex' => 'Format NIK salah.',
            'tanggal_aktif_kerja.before_or_equal' => 'Tanggal aktif kerja tidak boleh lebih dari hari ini.',
        ]);

        $validated['name'] = strtolower(strip_tags(trim($validated['name'])));
        $validated['email'] = strtolower(trim($validated['email']));

        $user->fill($validated)->save();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }

    public function resetPassword(User $user)
    {
        try {
            $defaultPassword = config('app.default_user_password', 'password123');
            $user->update([
                'password' => Hash::make($defaultPassword),
                'must_change_password' => true
            ]);
            return back()->with('success', "Password user berhasil direset ke default. User akan dipaksa mengganti password saat login berikutnya.");
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mereset password. Silakan coba lagi.');
        }
    }

    public function resetAllPasswords(Request $request)
    {
        try {
            $defaultPassword = config('app.default_user_password', 'password123');
            $updatedCount = User::whereNotIn('role', ['super_admin'])
                ->update([
                    'password' => Hash::make($defaultPassword),
                    'must_change_password' => true
                ]);


            return redirect()->route('admin.users.index')->with('success', "Berhasil mereset password {$updatedCount} user ke default. Semua user akan dipaksa mengganti password saat login berikutnya.");
        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')->with('error', 'Terjadi kesalahan saat mereset semua password. Silakan coba lagi.');
        }
    }
    public function destroyAll(Request $request)
    {
        try {
            DB::beginTransaction();

            $deletedCount = User::whereNotIn('role', ['super_admin'])->delete();

            DB::commit();

            if ($deletedCount > 0) {
                return redirect()->route('admin.users.index')->with('success', "Berhasil menghapus {$deletedCount} user. Super admin tetap aman.");
            } else {
                return redirect()->route('admin.users.index')->with('info', 'Tidak ada user yang dihapus. Hanya super admin yang tersisa.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.users.index')->with('error', 'Terjadi kesalahan saat menghapus user. Silakan coba lagi.');
        }
    }
}
