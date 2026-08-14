<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\LeavePrintController;
use App\Http\Controllers\MasterOfficeController;
use App\Http\Controllers\MasterPositionController;
use App\Http\Controllers\MasterLeaveTypeController;
use App\Http\Controllers\MasterUserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuotaController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\BackupController;


Route::get('/', function () {
    return redirect()->route('login');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

//route cuti & approve
Route::middleware(['auth'])->group(function () {

    Route::prefix('cuti')->name('cuti.')->group(function () {
        Route::resource('', LeaveController::class)
            ->parameters(['' => 'leave'])
            ->only(['index', 'create', 'store', 'destroy']); // tambah edit/update/destroy kalau perlu
        Route::patch('/{leave}/accept-revision', [LeaveController::class, 'acceptRevision'])->name('accept-revision');
        Route::patch('/{leave}/reject-revision', [LeaveController::class, 'rejectRevision'])->name('reject-revision');
    });

    Route::get('cuti/{leave}/print', [LeavePrintController::class, 'print'])
        ->name('cuti.print');

    Route::get('/replacements', [LeaveController::class, 'replacements'])->name('replacements.index');

    Route::prefix('approval')->name('approval.')->group(function () {
        Route::get('/', [ApprovalController::class, 'index'])->name('index');
        Route::get('/history', [ApprovalController::class, 'history'])->name('history');
        Route::patch('/{approval}/approve', [ApprovalController::class, 'approve'])->name('approve');
        Route::patch('/{approval}/reject', [ApprovalController::class, 'reject'])->name('reject');
        Route::patch('/{approval}/request-revision', [ApprovalController::class, 'requestRevision'])->name('request-revision');
    });

    // Routes untuk notifikasi
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('markAsRead');
        Route::patch('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('markAllAsRead');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unreadCount');
        Route::get('/latest', [NotificationController::class, 'getLatest'])->name('latest');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/force-change-password', function () {
        return view('auth.force-change-password');
    })->name('password.force-change');
});


// route master for super admin
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('divisions', DivisionController::class);
    Route::resource('positions', MasterPositionController::class);
    Route::resource('offices', MasterOfficeController::class);
    Route::resource('leave-types', MasterLeaveTypeController::class);
    Route::patch('leave-types/{leaveType}/toggle', [MasterLeaveTypeController::class, 'toggle'])->name('leave-types.toggle');
    Route::resource('users', MasterUserController::class);
    Route::post('users/{user}/reset-password', [MasterUserController::class, 'resetPassword'])->name('users.resetPassword');
    Route::post('/users/reset-passwords', [MasterUserController::class, 'resetAllPasswords'])
        ->name('users.resetAllPasswords');
    Route::delete('/destroy-all', [MasterUserController::class, 'destroyAll'])->name('users.destroyAll');
    Route::get('user-activity', [UserManagementController::class, 'index'])->name('user-activity.index');
    Route::patch('user-activity/{id}/approve', [UserManagementController::class, 'approve'])->name('user-activity.approve');
    Route::patch('user-activity/{id}/reject', [UserManagementController::class, 'reject'])->name('user-activity.reject');
});

// route rekap cuti
Route::middleware(['auth', 'role:hrd,super_admin'])->group(function () {

    Route::prefix('hrd')->name('hrd.')->group(function () {
        Route::get('rekap', [RekapController::class, 'index'])->name('rekap.index');
        Route::get('rekap/export', [\App\Http\Controllers\RekapController::class, 'export'])->name('rekap.export');
    });
    // backup db
    Route::prefix('backup')->name('backup.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::post('export', [BackupController::class, 'export'])->name('export');
        Route::post('import', [BackupController::class, 'import'])->name('import');
        Route::get('/download/{file}', [BackupController::class, 'download'])->name('download');
    });
});


// route set kuota cuti
Route::middleware(['auth', 'role:hrd,super_admin'])->prefix('hrd')->name('hrd.')->group(function () {
    Route::get('quota', [QuotaController::class, 'index'])->name('quota.index');
    Route::post('quota/reset', [QuotaController::class, 'resetAll'])->name('quota.reset');
    Route::post('quota/reset-division', [QuotaController::class, 'resetDivision'])->name('quota.resetDivision');
    Route::post('quota/reset-position', [QuotaController::class, 'resetPosition'])->name('quota.resetPosition');
    Route::post('quota/{user}/{leaveType}', [QuotaController::class, 'update'])->name('quota.update');
    Route::post('quota/settings', [QuotaController::class, 'updateSettings'])->name('quota.settings');
    Route::post('quota/generate-annual', [QuotaController::class, 'generateAnnualBalances'])->name('quota.generateAnnual');
});

require __DIR__ . '/auth.php';
