<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class DatabaseMaintenanceService
{
    private const BACKUP_DIR   = 'backups';
    private const RESTORE_TEMP = 'restore_temp';

    // ─────────────────────────────────────────────────────────
    // KONEKSI & INFO DATABASE
    // ─────────────────────────────────────────────────────────

    public function getConnectionStatus(): array
    {
        try {
            $pdo    = DB::connection()->getPdo();
            $dbName = DB::connection()->getDatabaseName();

            return [
                'connected' => true,
                'database'  => $dbName,
                'driver'    => config('database.default'),
                'host'      => config('database.connections.' . config('database.default') . '.host'),
                'port'      => config('database.connections.' . config('database.default') . '.port'),
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'error'     => $e->getMessage(),
            ];
        }
    }

    public function getDatabaseInfo(): array
    {
        try {
            $dbName = DB::connection()->getDatabaseName();

            $version = DB::selectOne('SELECT VERSION() as ver')?->ver ?? 'N/A';

            $sizeResult = DB::selectOne(
                'SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
                 FROM information_schema.tables WHERE table_schema = ?',
                [$dbName]
            );

            $tableCount = DB::selectOne(
                'SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = ?',
                [$dbName]
            )?->cnt ?? 0;

            $charset = DB::selectOne(
                'SELECT DEFAULT_CHARACTER_SET_NAME as charset, DEFAULT_COLLATION_NAME as collation
                 FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?',
                [$dbName]
            );

            // Top 20 tabel terbesar
            $tables = DB::select(
                'SELECT table_name as table_name, IFNULL(table_rows, 0) as table_rows,
                        ROUND((data_length + index_length) / 1024, 2) as size_kb,
                        engine as engine
                 FROM information_schema.tables
                 WHERE table_schema = ?
                 ORDER BY (data_length + index_length) DESC
                 LIMIT 20',
                [$dbName]
            );

            return [
                'success'    => true,
                'database'   => $dbName,
                'version'    => $version,
                'size_mb'    => (float) ($sizeResult?->size_mb ?? 0),
                'size_human' => $this->formatBytes((int)(($sizeResult?->size_mb ?? 0) * 1048576)),
                'tables'     => (int) $tableCount,
                'charset'    => $charset?->charset ?? 'N/A',
                'collation'  => $charset?->collation ?? 'N/A',
                'table_list' => $tables,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────────────────
    // DISK INFO
    // ─────────────────────────────────────────────────────────

    public function getDiskInfo(): array
    {
        $this->ensureDir(self::BACKUP_DIR);
        $path = Storage::disk('local')->path(self::BACKUP_DIR);

        $free  = (int) disk_free_space($path);
        $total = (int) disk_total_space($path);

        return [
            'free_bytes'   => $free,
            'total_bytes'  => $total,
            'free_human'   => $this->formatBytes($free),
            'total_human'  => $this->formatBytes($total),
            'used_percent' => $total > 0 ? round((($total - $free) / $total) * 100, 1) : 0,
        ];
    }

    public function checkDiskSpace(): array
    {
        $dbInfo  = $this->getDatabaseInfo();
        $diskInfo = $this->getDiskInfo();

        if (! $dbInfo['success']) {
            return ['sufficient' => true]; // gagal cek DB — biarkan lanjut
        }

        $dbSizeBytes = $dbInfo['size_mb'] * 1048576;
        $required    = (int) ($dbSizeBytes * 1.5); // buffer 50%

        return [
            'sufficient' => $diskInfo['free_bytes'] > $required,
            'free'       => $diskInfo['free_human'],
            'required'   => $this->formatBytes($required),
        ];
    }

    // ─────────────────────────────────────────────────────────
    // BACKUP
    // ─────────────────────────────────────────────────────────

    public function listBackups(): array
    {
        $this->ensureDir(self::BACKUP_DIR);
        $files   = Storage::disk('local')->files(self::BACKUP_DIR);
        $backups = [];

        foreach ($files as $file) {
            if (! str_ends_with($file, '.sql')) continue;

            $fullPath = Storage::disk('local')->path($file);
            $size     = (int) filesize($fullPath);
            $mtime    = (int) filemtime($fullPath);

            $backups[] = [
                'filename'   => basename($file),
                'size'       => $size,
                'size_human' => $this->formatBytes($size),
                'created_at' => Carbon::createFromTimestamp($mtime),
            ];
        }

        usort(
            $backups,
            fn($a, $b) =>
            $b['created_at']->timestamp - $a['created_at']->timestamp
        );

        return $backups;
    }

    public function createBackup(): array
    {
        $this->ensureDir(self::BACKUP_DIR);

        $mysqldump = $this->findExecutable('mysqldump');
        if (! $mysqldump) {
            return [
                'success' => false,
                'message' => 'Perintah mysqldump tidak ditemukan. Pastikan MySQL Client terinstal dan tersedia di PATH server.',
            ];
        }

        $disk = $this->checkDiskSpace();
        if (! $disk['sufficient']) {
            return [
                'success' => false,
                'message' => "Ruang disk tidak cukup. Tersedia: {$disk['free']}, Dibutuhkan: {$disk['required']}.",
            ];
        }

        $config     = $this->getDbConfig();
        $filename   = 'backup_' . $config['database'] . '_' . now()->format('Ymd_His') . '.sql';
        $outputPath = Storage::disk('local')->path(self::BACKUP_DIR . '/' . $filename);
        $configFile = $this->writeTempConfig($config);

        try {
            // --defaults-extra-file harus menjadi argumen PERTAMA
            $command = [
                $mysqldump,
                '--defaults-extra-file=' . $configFile,
                '--host='   . $config['host'],
                '--port='   . $config['port'],
                '--protocol=TCP',

                '--single-transaction',
                '--routines',
                '--triggers',
                '--events',
                '--add-drop-table',
                '--create-options',
                '--disable-keys',
                '--extended-insert',
                '--set-charset',
                '--result-file=' . $outputPath,

                $config['database'],
            ];

            [$exitCode, $errorOutput] = $this->runProcess($command);

            if ($exitCode !== 0) {
                @unlink($outputPath);
                Log::error('[DatabaseMaintenance] mysqldump gagal', [
                    'exit_code' => $exitCode,
                    'error'     => $errorOutput,
                    'command'   => implode(' ', array_map(fn($a) => '"' . $a . '"', $command)),
                ]);
                return [
                    'success' => false,
                    'message' => 'mysqldump gagal: ' . trim($errorOutput),
                ];
            }

            if (! file_exists($outputPath) || filesize($outputPath) === 0) {
                return [
                    'success' => false,
                    'message' => 'File backup tidak terbentuk atau kosong.',
                ];
            }

            $size = (int) filesize($outputPath);

            return [
                'success'    => true,
                'filename'   => $filename,
                'size'       => $size,
                'size_human' => $this->formatBytes($size),
                'message'    => "Backup berhasil: {$filename} ({$this->formatBytes($size)})",
            ];
        } finally {
            if (file_exists($configFile)) @unlink($configFile);
        }
    }

    public function backupExists(string $filename): bool
    {
        if (! preg_match('/^[a-zA-Z0-9._-]+\.sql$/', $filename)) return false;
        return Storage::disk('local')->exists(self::BACKUP_DIR . '/' . $filename);
    }

    public function getBackupPath(string $filename): string
    {
        return Storage::disk('local')->path(self::BACKUP_DIR . '/' . $filename);
    }

    public function deleteBackup(string $filename): bool
    {
        if (! $this->backupExists($filename)) return false;
        return Storage::disk('local')->delete(self::BACKUP_DIR . '/' . $filename);
    }

    // ─────────────────────────────────────────────────────────
    // RESTORE
    // ─────────────────────────────────────────────────────────

    public function validateSqlFile(string $filePath): array
    {
        if (! file_exists($filePath)) {
            return ['valid' => false, 'message' => 'File tidak ditemukan.'];
        }

        $size = (int) filesize($filePath);

        if ($size === 0) {
            return ['valid' => false, 'message' => 'File SQL kosong.'];
        }

        // Batas 512 MB
        if ($size > 536870912) {
            return [
                'valid'   => false,
                'message' => 'Ukuran file melebihi batas 512 MB. Gunakan import manual via CLI.',
            ];
        }

        // Baca 8KB pertama untuk validasi konten
        $handle = fopen($filePath, 'r');
        $header = fread($handle, 8192);
        fclose($handle);

        $sqlKeywords = [
            'CREATE TABLE',
            'INSERT INTO',
            'DROP TABLE',
            'mysqldump',
            '-- MySQL',
            '-- MariaDB',
            'SET SQL_MODE',
            'SET NAMES',
            'USE `',
        ];

        $hasSql = false;
        foreach ($sqlKeywords as $keyword) {
            if (stripos($header, $keyword) !== false) {
                $hasSql = true;
                break;
            }
        }

        if (! $hasSql) {
            return [
                'valid'   => false,
                'message' => 'File tidak terdeteksi sebagai SQL dump MySQL yang valid.',
            ];
        }

        return [
            'valid'      => true,
            'size'       => $size,
            'size_human' => $this->formatBytes($size),
        ];
    }

    public function restore(string $sqlFilePath): array
    {
        $mysql = $this->findExecutable('mysql');

        if (! $mysql) {
            // Fallback: native PHP restore via PDO
            return $this->restoreViaPdo($sqlFilePath);
        }

        $config     = $this->getDbConfig();
        $configFile = $this->writeTempConfig($config);

        try {
            $command = [
                $mysql,
                '--defaults-extra-file=' . $configFile,
                '--host='     . $config['host'],
                '--port='     . $config['port'],
                '--protocol=TCP',
                $config['database'],
            ];

            [$exitCode, $errorOutput] = $this->runProcess($command, $sqlFilePath);

            if ($exitCode !== 0) {
                $errorMsg = trim($errorOutput);
                Log::error('[DatabaseMaintenance] Restore gagal via CLI', [
                    'exit_code' => $exitCode,
                    'error'     => $errorMsg,
                ]);

                // Jika CLI gagal karena socket Windows, coba fallback PDO
                if (
                    str_contains($errorMsg, '10106') ||
                    str_contains($errorMsg, 'TCP/IP socket') ||
                    str_contains($errorMsg, 'Can\'t connect')
                ) {
                    Log::info('[DatabaseMaintenance] Fallback ke PDO restore...');
                    return $this->restoreViaPdo($sqlFilePath);
                }

                return [
                    'success' => false,
                    'message' => 'Restore gagal: ' . $errorMsg,
                ];
            }

            return ['success' => true, 'message' => 'Database berhasil dipulihkan.'];
        } finally {
            if (file_exists($configFile)) @unlink($configFile);
        }
    }

    public function getRestoreTempDir(): string
    {
        $path = storage_path('app/' . self::RESTORE_TEMP);
        if (! is_dir($path)) mkdir($path, 0755, true);
        return $path;
    }

    public function cleanRestoreTemp(): void
    {
        $dir = storage_path('app/' . self::RESTORE_TEMP);
        if (! is_dir($dir)) return;

        foreach (glob($dir . '/*.sql') as $file) {
            // Hapus file temp yang lebih dari 2 jam
            if (filemtime($file) < time() - 7200) {
                @unlink($file);
            }
        }
    }

    // ─────────────────────────────────────────────────────────
    // UTILITY
    // ─────────────────────────────────────────────────────────

    public function canRunProcess(): bool
    {
        return function_exists('proc_open');
    }

    public function findExecutable(string $name): ?string
    {
        // 1. Cari dari PATH (gunakan PATH yang sedang aktif)
        $isWin = PHP_OS_FAMILY === 'Windows';
        $cmd   = $isWin ? "where {$name}.exe 2>NUL" : "which {$name} 2>/dev/null";

        $output = [];
        $code   = 0;
        @exec($cmd, $output, $code);

        if ($code === 0 && !empty($output[0]) && is_file(trim($output[0]))) {
            return trim($output[0]);
        }

        // 2. Fallback lokasi umum
        $candidates = [];

        if ($isWin) {
            $ext = '.exe';

            // Laragon (scan semua versi MySQL yang ada)
            $laragonPaths = glob('C:\\laragon\\bin\\mysql\\mysql-*\\bin\\' . $name . $ext);
            if ($laragonPaths) {
                $candidates = array_merge($candidates, $laragonPaths);
            }

            $candidates = array_merge($candidates, [
                "C:\\xampp\\mysql\\bin\\{$name}{$ext}",
                "C:\\xampp64\\mysql\\bin\\{$name}{$ext}",
                "C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\{$name}{$ext}",
                "C:\\Program Files\\MySQL\\MySQL Server 8.3\\bin\\{$name}{$ext}",
                "C:\\Program Files\\MySQL\\MySQL Server 8.4\\bin\\{$name}{$ext}",
                "C:\\Program Files\\MySQL\\MySQL Server 9.0\\bin\\{$name}{$ext}",
            ]);

            // Scan WAMP otomatis
            $wampPaths = glob('C:\\wamp64\\bin\\mysql\\*\\bin\\' . $name . $ext) ?: [];
            $candidates = array_merge($candidates, $wampPaths);
        } else {
            $candidates = [
                "/usr/bin/{$name}",
                "/usr/local/bin/{$name}",
                "/usr/local/mysql/bin/{$name}",
                "/opt/homebrew/bin/{$name}",
                "/opt/local/bin/{$name}",
            ];
        }

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    public function formatBytes(int $bytes): string
    {
        if ($bytes <= 0)         return '0 B';
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)       return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    // ─────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────

    /**
     * Jalankan proses eksternal dengan proc_open agar environment
     * Windows diwarisi dengan benar (menghindari WinSock error 10106).
     *
     * @param  array       $command   Array perintah + argumen
     * @param  string|null $stdinFile Path file yang dijadikan stdin (untuk restore)
     * @return array{0: int, 1: string}  [exitCode, errorOutput]
     */
    private function runProcess(array $command, ?string $stdinFile = null): array
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return $this->runProcessWindows($command, $stdinFile);
        }

        // Unix: Symfony Process sudah reliable
        $process = new Process($command);
        $process->setTimeout(900);
        if ($stdinFile) {
            $process->setInput(fopen($stdinFile, 'r'));
        }
        $process->run();

        return [$process->getExitCode() ?? 1, $process->getErrorOutput()];
    }

    /**
     * Implementasi proses untuk Windows menggunakan proc_open agar
     * environment (termasuk Winsock) diwarisi dari proses parent (PHP/web server).
     * Ini menghindari error "Can't create TCP/IP socket (10106)".
     */
    private function runProcessWindows(array $command, ?string $stdinFile = null): array
    {
        // Escape setiap argumen untuk shell Windows
        $escaped = array_map(fn($arg) => '"' . str_replace('"', '""', $arg) . '"', $command);
        $cmdLine  = implode(' ', $escaped);

        $stdinMode  = $stdinFile ? ['file', $stdinFile, 'r'] : ['pipe', 'r'];
        $descriptors = [
            0 => $stdinMode,       // stdin
            1 => ['pipe', 'w'],    // stdout
            2 => ['pipe', 'w'],    // stderr
        ];

        // Warisi environment saat ini — KRITIS untuk Winsock di Windows
        $env = null; // null = warisi environment dari parent process

        $process = proc_open($cmdLine, $descriptors, $pipes, null, $env);

        if (! is_resource($process)) {
            return [1, 'Gagal memulai proses eksternal.'];
        }

        // Jika stdin adalah pipe (bukan file), tutup segera
        if (! $stdinFile) {
            fclose($pipes[0]);
        }

        // Baca stdout & stderr secara paralel untuk mencegah deadlock buffer
        $stdout = '';
        $stderr = '';

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        while (true) {
            $stdout .= stream_get_contents($pipes[1]);
            $stderr .= stream_get_contents($pipes[2]);

            $status = proc_get_status($process);
            if (! $status['running']) {
                // Baca sisa output
                $stdout .= stream_get_contents($pipes[1]);
                $stderr .= stream_get_contents($pipes[2]);
                break;
            }

            usleep(50000); // 50ms polling
        }

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        // proc_close kadang return -1 di Windows, fallback ke status
        if ($exitCode === -1) {
            $exitCode = empty($stderr) ? 0 : 1;
        }

        return [$exitCode, $stderr];
    }

    /**
     * Fallback restore menggunakan PDO (tanpa CLI mysql).
     * Cocok untuk kasus mysqldump CLI tidak tersedia atau bermasalah.
     *
     * PERINGATAN: Untuk file SQL sangat besar (>100MB), gunakan CLI.
     */
    private function restoreViaPdo(string $sqlFilePath): array
    {
        try {
            $pdo = DB::connection()->getPdo();

            // Nonaktifkan foreign key checks sementara
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
            $pdo->exec('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"');
            $pdo->exec('SET time_zone="+00:00"');

            $handle    = fopen($sqlFilePath, 'r');
            $buffer    = '';
            $delimiter = ';';
            $lineNum   = 0;
            $errors    = [];

            while (! feof($handle)) {
                $line = fgets($handle);
                $lineNum++;

                if ($line === false) break;

                // Lewati komentar dan baris kosong
                $trimmed = ltrim($line);
                if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
                    continue;
                }

                // Handle DELIMITER custom (stored procedures)
                if (preg_match('/^\s*DELIMITER\s+(\S+)/i', $trimmed, $m)) {
                    $delimiter = $m[1];
                    continue;
                }

                $buffer .= $line;

                // Cek apakah statement selesai (diakhiri delimiter)
                $bufTrimmed = rtrim($buffer);
                if (str_ends_with($bufTrimmed, $delimiter)) {
                    $stmt = $delimiter !== ';'
                        ? rtrim(substr($bufTrimmed, 0, -strlen($delimiter))) . ';'
                        : $bufTrimmed;

                    $stmt = trim($stmt);
                    if ($stmt !== '' && $stmt !== ';') {
                        try {
                            $pdo->exec($stmt);
                        } catch (\PDOException $e) {
                            // Lanjutkan meski ada error non-fatal
                            $errors[] = "Baris ~{$lineNum}: " . $e->getMessage();
                            Log::warning('[DatabaseMaintenance] PDO restore warning', [
                                'line'  => $lineNum,
                                'error' => $e->getMessage(),
                                'stmt'  => substr($stmt, 0, 200),
                            ]);
                        }
                    }
                    $buffer = '';
                }
            }

            fclose($handle);
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

            if (count($errors) > 5) {
                return [
                    'success' => false,
                    'message' => 'Restore selesai dengan banyak error (' . count($errors) . ' error). Periksa log untuk detail.',
                ];
            }

            $warnMsg = count($errors) > 0
                ? ' (dengan ' . count($errors) . ' peringatan minor, lihat log)'
                : '';

            return [
                'success' => true,
                'message' => 'Database berhasil dipulihkan via PDO' . $warnMsg . '.',
            ];
        } catch (\Exception $e) {
            Log::error('[DatabaseMaintenance] PDO restore error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Restore gagal: ' . $e->getMessage(),
            ];
        }
    }

    private function getDbConfig(): array
    {
        $conn = config('database.default');
        $cfg  = config("database.connections.{$conn}");

        return [
            'host'     => $cfg['host']     ?? '127.0.0.1',
            'port'     => (string) ($cfg['port']     ?? 3306),
            'database' => $cfg['database'] ?? '',
            'username' => $cfg['username'] ?? 'root',
            'password' => $cfg['password'] ?? '',
        ];
    }

    /**
     * Tulis kredensial ke temp file — password tidak terekspos di process list.
     * Format .cnf MySQL yang kompatibel.
     */
    private function writeTempConfig(array $config): string
    {
        // Gunakan storage_path agar PHP web server punya akses tulis yang pasti
        $tmpDir  = storage_path('app/tmp_cnf');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0700, true);
        }

        $tmpFile = $tmpDir . DIRECTORY_SEPARATOR . 'mydb_' . bin2hex(random_bytes(8)) . '.cnf';

        $content = implode("\n", [
            '[client]',
            'user="'     . str_replace('"', '\\"', $config['username']) . '"',
            'password="' . str_replace('"', '\\"', $config['password']) . '"',
            'host="'     . $config['host'] . '"',
            'port='      . $config['port'],
            '',
        ]);

        file_put_contents($tmpFile, $content);

        if (PHP_OS_FAMILY !== 'Windows') {
            chmod($tmpFile, 0600);
        }

        return $tmpFile;
    }

    private function ensureDir(string $dir): void
    {
        if (! Storage::disk('local')->exists($dir)) {
            Storage::disk('local')->makeDirectory($dir);
        }
    }
}
