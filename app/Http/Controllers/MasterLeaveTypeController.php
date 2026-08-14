<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\Request;
use App\Http\Requests\StoreLeaveTypeRequest;
use App\Http\Requests\UpdateLeaveTypeRequest;

class MasterLeaveTypeController extends Controller
{
    /**
     * Display a listing of the leave types.
     */
    public function index(Request $request)
    {
        $leaveTypes = LeaveType::query()
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->paginate(10);

        return view('admin.leave-types.index', compact('leaveTypes'));
    }

    /**
     * Show the form for creating a new leave type.
     */
    public function create()
    {
        return view('admin.leave-types.create');
    }

    /**
     * Store a newly created leave type in storage.
     */
    public function store(StoreLeaveTypeRequest $request)
    {
        LeaveType::create($request->validated());

        return redirect()->route('admin.leave-types.index')
            ->with('success', 'Jenis cuti berhasil ditambahkan.');
    }

    /**
     * Display the specified leave type.
     */
    public function show(LeaveType $leaveType)
    {
        return view('admin.leave-types.show', compact('leaveType'));
    }

    /**
     * Show the form for editing the specified leave type.
     */
    public function edit(LeaveType $leaveType)
    {
        return view('admin.leave-types.edit', compact('leaveType'));
    }

    /**
     * Update the specified leave type in storage.
     */
    public function update(UpdateLeaveTypeRequest $request, LeaveType $leaveType)
    {
        $leaveType->update($request->validated());

        return redirect()->route('admin.leave-types.index')
            ->with('success', 'Jenis cuti berhasil diperbarui.');
    }

    /**
     * Remove the specified leave type from storage.
     */
    public function destroy(LeaveType $leaveType)
    {
        // Check if leave type is being used
        if ($leaveType->leaves()->exists()) {
            return back()->withErrors(['msg' => 'Jenis cuti tidak dapat dihapus karena masih digunakan dalam pengajuan cuti.']);
        }

        if ($leaveType->userLeaveBalances()->exists()) {
            return back()->withErrors(['msg' => 'Jenis cuti tidak dapat dihapus karena masih memiliki saldo cuti pengguna.']);
        }

        $leaveType->delete();

        return redirect()->route('admin.leave-types.index')->with('success', 'Jenis cuti berhasil dihapus.');
    }

    /**
     * Toggle the active status of the specified leave type.
     */
    public function toggle(LeaveType $leaveType)
    {
        $leaveType->update([
            'is_active' => !$leaveType->is_active,
        ]);

        $status = $leaveType->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()->with('success', "Jenis cuti {$leaveType->name} berhasil {$status}.");
    }
}
