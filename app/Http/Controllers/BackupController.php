<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;
use App\Http\Controllers\Auth;

class BackupController extends Controller
{
    /**
     * Halaman utama backup/restore
     */
    public function index()
    {
        // Ambil daftar file backup yang tersimpan (opsional)
        $backupFiles = [];
        $backupDir = storage_path('database');
        if (File::exists($backupDir)) {
            $files = File::files($backupDir);
            foreach ($files as $file) {
                $backupFiles[] = [
                    'name' => $file->getFilename(),
                    'size' => $this->formatBytes($file->getSize()),
                    'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                    'path' => $file->getPathname()
                ];
            }
            // Urutkan dari yang terbaru
            usort($backupFiles, fn($a, $b) => strtotime($b['modified']) - strtotime($a['modified']));
        }

        return view('admin.backup.index', compact('backupFiles'));
    }

    /**
     * Ekspor database ke file SQL
     */
    public function export(Request $request)
    {
        ini_set('max_execution_time', 300); // 5 menit untuk ekspor besar
        $compress = $request->boolean('compress', false);

        try {
            // Dapatkan semua nama tabel
            $tables = DB::select('SHOW TABLES');
            $databaseName = DB::getDatabaseName();
            $tableKey = 'Tables_in_' . $databaseName;

            $sql = "-- -------------------------------------\n";
            $sql .= "-- Backup Database: " . $databaseName . "\n";
            $sql .= "-- Tanggal: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- -------------------------------------\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;
                $sql .= $this->getTableStructure($tableName);
                $sql .= $this->getTableData($tableName);
                $sql .= "\n";
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            // Simpan file sementara
            $filename = 'backup_' . date('Ymd_His') . '.sql';
            $storagePath = storage_path('database/' . $filename);

            if (!File::exists(storage_path('database'))) {
                File::makeDirectory(storage_path('database'), 0755, true);
            }

            File::put($storagePath, $sql);

            // Jika perlu kompresi
            if ($compress) {
                $zipPath = str_replace('.sql', '.zip', $storagePath);
                $zip = new ZipArchive();
                if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
                    $zip->addFile($storagePath, $filename);
                    $zip->close();
                }
                // Hapus file SQL sementara
                File::delete($storagePath);
                $downloadFile = $zipPath;
                $downloadName = 'backup_' . date('Ymd_His') . '.zip';
            } else {
                $downloadFile = $storagePath;
                $downloadName = $filename;
            }

            return response()->download($downloadFile, $downloadName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Ekspor database gagal: ' . $e->getMessage());
        }
    }

    /**
     * Import database dari file SQL
     */
    public function import(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|max:51200'
        ]);

        $file = $request->file('sql_file');
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['sql', 'zip'])) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'File harus berekstensi .sql atau .zip'], 422);
            }
            return back()->withErrors(['sql_file' => 'File harus berekstensi .sql atau .zip']);
        }

        ini_set('max_execution_time', 600);
        set_time_limit(600);

        try {
            $sqlContent = '';

            if ($extension === 'zip') {
                $zip = new \ZipArchive();
                if ($zip->open($file->getPathname()) === true) {
                    $extractPath = storage_path('database/temp_import');
                    if (!File::exists($extractPath)) {
                        File::makeDirectory($extractPath, 0755, true);
                    }
                    $zip->extractTo($extractPath);
                    $zip->close();

                    $sqlFiles = File::glob($extractPath . '/*.sql');
                    if (empty($sqlFiles)) {
                        throw new \Exception('File ZIP tidak mengandung file .sql');
                    }
                    $sqlContent = File::get($sqlFiles[0]);
                    File::deleteDirectory($extractPath);
                } else {
                    throw new \Exception('Tidak dapat membuka file ZIP');
                }
            } else {
                $sqlContent = File::get($file->getPathname());
            }

            // Backup otomatis sebelum import
            $backupPath = storage_path('database/pre_import_' . date('Ymd_His') . '.sql');
            if (!File::exists(storage_path('database'))) {
                File::makeDirectory(storage_path('database'), 0755, true);
            }
            $this->quickBackup($backupPath);

            // Mulai transaksi
            DB::beginTransaction();

            try {
                // Matikan foreign key checks
                DB::statement('SET FOREIGN_KEY_CHECKS=0');

                $queries = $this->splitSqlQueries($sqlContent);
                $processed = 0;
                foreach ($queries as $query) {
                    $query = trim($query);
                    if (empty($query)) continue;
                    if (str_starts_with($query, '--') || str_starts_with($query, '#')) continue;
                    DB::unprepared($query);
                    $processed++;
                }

                // Aktifkan kembali foreign key checks
                DB::statement('SET FOREIGN_KEY_CHECKS=1');

                DB::commit();

                // Logout setelah import sukses
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->ajax()) {
                    return response()->json(['success' => true, 'message' => "Import berhasil! {$processed} query dieksekusi. Silakan login kembali."]);
                }
                return redirect()->route('login')->with('success', "Import berhasil! {$processed} query dieksekusi. Silakan login kembali.");
            } catch (\Exception $e) {
                // Rollback hanya jika transaksi masih aktif
                if (DB::transactionLevel() > 0) {
                    DB::rollBack();
                }
                // Pastikan foreign key diaktifkan lagi
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
                throw $e;
            }
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Import gagal: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function download($file)
    {
        $path = storage_path('database/' . basename($file));
        if (!File::exists($path)) {
            abort(404);
        }
        return response()->download($path);
    }

    /**
     * Backup cepat tanpa download (untuk keamanan sebelum import)
     */
    private function quickBackup($path)
    {
        $tables = DB::select('SHOW TABLES');
        $databaseName = DB::getDatabaseName();
        $tableKey = 'Tables_in_' . $databaseName;
        $sql = "-- Pre-import backup\n";

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            $sql .= $this->getTableStructure($tableName);
            $sql .= $this->getTableData($tableName);
        }
        File::put($path, $sql);
    }

    /**
     * Generate struktur CREATE TABLE
     */
    private function getTableStructure($tableName)
    {
        $result = DB::select("SHOW CREATE TABLE `{$tableName}`");
        $createTable = $result[0]->{'Create Table'} ?? '';
        return "DROP TABLE IF EXISTS `{$tableName}`;\n{$createTable};\n\n";
    }

    /**
     * Generate INSERT data per baris
     */
    private function getTableData($tableName)
    {
        $rows = DB::table($tableName)->get();
        if ($rows->isEmpty()) {
            return "";
        }

        $sql = "";
        $columns = array_keys((array) $rows[0]);
        $columnsEscaped = array_map(fn($col) => "`{$col}`", $columns);
        $columnsList = implode(',', $columnsEscaped);

        $batchSize = 50;
        $batches = $rows->chunk($batchSize);

        foreach ($batches as $batch) {
            $values = [];
            foreach ($batch as $row) {
                $rowValues = [];
                foreach ($columns as $col) {
                    $value = $row->$col;
                    if (is_null($value)) {
                        $rowValues[] = "NULL";
                    } elseif (is_numeric($value)) {
                        $rowValues[] = $value;
                    } else {
                        $rowValues[] = "'" . addslashes($value) . "'";
                    }
                }
                $values[] = "(" . implode(',', $rowValues) . ")";
            }
            $sql .= "INSERT INTO `{$tableName}` ({$columnsList}) VALUES " . implode(",\n", $values) . ";\n";
        }

        return $sql . "\n";
    }

    /**
     * Split SQL file menjadi array queries (handles delimiter)
     */
    private function splitSqlQueries($sql)
    {
        $queries = [];
        $buffer = "";
        $inString = false;
        $stringChar = '';
        $len = strlen($sql);

        for ($i = 0; $i < $len; $i++) {
            $char = $sql[$i];
            $nextChar = $i + 1 < $len ? $sql[$i + 1] : '';

            if (!$inString && $char == ';') {
                $queries[] = trim($buffer);
                $buffer = "";
                continue;
            }

            if (!$inString && ($char == "'" || $char == '"')) {
                $inString = true;
                $stringChar = $char;
                $buffer .= $char;
                continue;
            }

            if ($inString && $char == $stringChar && $nextChar != $stringChar) {
                $inString = false;
                $buffer .= $char;
                continue;
            }

            $buffer .= $char;
        }

        if (trim($buffer)) {
            $queries[] = trim($buffer);
        }

        return $queries;
    }

    /**
     * Format bytes ke human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
