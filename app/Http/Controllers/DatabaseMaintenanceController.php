<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DatabaseMaintenanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DatabaseMaintenanceController extends Controller
{
    public function __construct(
        private readonly DatabaseMaintenanceService $service,
    ) {}

    // ─── Index ────────────────────────────────────────────────

    public function index(): View
    {
        // Bersihkan file restore temp yang expired
        $this->service->cleanRestoreTemp();

        return view('admin.database.index', [
            'title'          => 'Database Maintenance',
            'subtitle'       => 'Monitoring, backup, dan restore database sistem',
            'canRunProcess'  => $this->service->canRunProcess(),
            'mysqldumpFound' => $this->service->findExecutable('mysqldump') !== null,
            'mysqlFound'     => $this->service->findExecutable('mysql') !== null,
            'connection'     => $this->service->getConnectionStatus(),
            'dbInfo'         => $this->service->getDatabaseInfo(),
            'backups'        => $this->service->listBackups(),
            'diskInfo'       => $this->service->getDiskInfo(),
            'diskCheck'      => $this->service->checkDiskSpace(),
            'restorePending' => session('restore_pending'),
        ]);
    }

    // ─── Backup ───────────────────────────────────────────────

    public function backup(): RedirectResponse
    {
        if (! $this->service->canRunProcess()) {
            return redirect()->route('database.index')
                ->with('db_error', 'Fungsi proc_open tidak tersedia di server ini. Hubungi administrator server.');
        }

        $result = $this->service->createBackup();

        if ($result['success']) {
            activity('database')
                ->causedBy(auth()->user())
                ->withProperties([
                    'filename' => $result['filename'],
                    'size'     => $result['size_human'],
                ])
                ->log("Backup berhasil dibuat: {$result['filename']} ({$result['size_human']})");

            return redirect()->route('database.index')
                ->with('db_success', $result['message']);
        }

        activity('database')
            ->causedBy(auth()->user())
            ->withProperties(['error' => $result['message']])
            ->log('Backup database GAGAL: ' . $result['message']);

        return redirect()->route('database.index')
            ->with('db_error', $result['message']);
    }

    // ─── Download Backup ─────────────────────────────────────

    public function download(string $filename): mixed
    {
        if (! $this->service->backupExists($filename)) {
            return redirect()->route('database.index')
                ->with('db_error', 'File backup tidak ditemukan.');
        }

        activity('database')
            ->causedBy(auth()->user())
            ->withProperties(['filename' => $filename])
            ->log("Backup diunduh: {$filename}");

        $path = $this->service->getBackupPath($filename);
        $size = (int) filesize($path);

        return response()->streamDownload(function () use ($path) {
            $handle = fopen($path, 'rb');
            while (! feof($handle)) {
                echo fread($handle, 65536);
                flush();
            }
            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length'      => $size,
        ]);
    }

    // ─── Delete Backup ────────────────────────────────────────

    public function deleteBackup(string $filename): RedirectResponse
    {
        if (! $this->service->backupExists($filename)) {
            return redirect()->route('database.index')
                ->with('db_error', 'File backup tidak ditemukan.');
        }

        $this->service->deleteBackup($filename);

        activity('database')
            ->causedBy(auth()->user())
            ->withProperties(['filename' => $filename])
            ->log("Backup dihapus: {$filename}");

        return redirect()->route('database.index')
            ->with('db_success', "Backup {$filename} berhasil dihapus.");
    }

    // ─── Restore: Upload ─────────────────────────────────────

    public function uploadRestore(Request $request): RedirectResponse
    {
        $request->validate([
            'sql_file' => ['required', 'file', 'max:524288'],
        ], [
            'sql_file.required' => 'File SQL wajib dipilih.',
            'sql_file.max'      => 'Ukuran file maksimal 512 MB.',
        ]);

        $file = $request->file('sql_file');

        // Validasi ekstensi
        if (strtolower($file->getClientOriginalExtension()) !== 'sql') {
            return redirect()->route('database.index')
                ->with('db_error', 'File harus berformat .sql');
        }

        // Simpan ke temp directory
        $tempDir  = $this->service->getRestoreTempDir();
        $tempName = 'restore_' . uniqid() . '_' . time() . '.sql';
        $tempPath = $tempDir . '/' . $tempName;

        $file->move($tempDir, $tempName);

        // Validasi konten SQL
        $validation = $this->service->validateSqlFile($tempPath);
        if (! $validation['valid']) {
            @unlink($tempPath);
            return redirect()->route('database.index')
                ->with('db_error', 'File tidak valid: ' . $validation['message']);
        }

        // Simpan info ke session untuk konfirmasi
        session([
            'restore_pending' => [
                'tmp_path'      => $tempPath,
                'original_name' => $file->getClientOriginalName(),
                'size_human'    => $validation['size_human'],
                'size'          => $validation['size'],
                'uploaded_at'   => now()->toISOString(),
            ],
        ]);

        activity('database')
            ->causedBy(auth()->user())
            ->withProperties([
                'filename' => $file->getClientOriginalName(),
                'size'     => $validation['size_human'],
            ])
            ->log("File restore diunggah dan siap dikonfirmasi: {$file->getClientOriginalName()}");

        return redirect()->route('database.index')
            ->with('open_restore_modal', true);
    }

    // ─── Restore: Confirm ────────────────────────────────────

    public function confirmRestore(Request $request): RedirectResponse
    {
        $request->validate([
            'confirmation' => ['required', 'string'],
        ]);

        if ($request->input('confirmation') !== 'RESTORE') {
            return redirect()->route('database.index')
                ->withErrors(['restore_confirm' => 'Konfirmasi tidak valid. Ketik RESTORE (huruf kapital semua).'])
                ->with('open_restore_modal', true);
        }

        $pending = session('restore_pending');

        if (! $pending || ! file_exists($pending['tmp_path'])) {
            session()->forget('restore_pending');
            return redirect()->route('database.index')
                ->with('db_error', 'Sesi restore tidak valid atau sudah kedaluwarsa. Silakan unggah ulang file SQL.');
        }

        if (! $this->service->canRunProcess()) {
            @unlink($pending['tmp_path']);
            session()->forget('restore_pending');
            return redirect()->route('database.index')
                ->with('db_error', 'Fungsi proc_open tidak tersedia di server.');
        }

        // Log bahwa restore akan dimulai
        activity('database')
            ->causedBy(auth()->user())
            ->withProperties([
                'filename' => $pending['original_name'],
                'size'     => $pending['size_human'],
            ])
            ->log("RESTORE DATABASE dimulai dari: {$pending['original_name']}");

        $result = $this->service->restore($pending['tmp_path']);

        // Cleanup
        if (file_exists($pending['tmp_path'])) @unlink($pending['tmp_path']);
        session()->forget('restore_pending');

        if ($result['success']) {
            activity('database')
                ->causedBy(auth()->user())
                ->withProperties(['filename' => $pending['original_name']])
                ->log("RESTORE DATABASE selesai berhasil dari: {$pending['original_name']}");

            return redirect()->route('database.index')
                ->with('db_success', '✓ Database berhasil dipulihkan dari "' . $pending['original_name'] . '". Semua data telah diperbarui.');
        }

        activity('database')
            ->causedBy(auth()->user())
            ->withProperties([
                'filename' => $pending['original_name'],
                'error'    => $result['message'],
            ])
            ->log("RESTORE DATABASE GAGAL: {$result['message']}");

        return redirect()->route('database.index')
            ->with('db_error', $result['message']);
    }

    // ─── Dismiss Restore ─────────────────────────────────────

    public function dismissRestore(): RedirectResponse
    {
        $pending = session('restore_pending');
        if ($pending && file_exists($pending['tmp_path'] ?? '')) {
            @unlink($pending['tmp_path']);
        }
        session()->forget('restore_pending');

        return redirect()->route('database.index');
    }
}
