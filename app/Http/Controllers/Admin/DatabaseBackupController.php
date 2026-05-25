<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;


class DatabaseBackupController extends Controller
{
    /**
     * List all backups
     */
    public function index()
    {
        $backupPath = storage_path('app/backups');
        $backups = [];

        if (File::isDirectory($backupPath)) {
            $files = File::files($backupPath);
            foreach ($files as $file) {
                if ($file->getExtension() === 'sql' || $file->getExtension() === 'gz') {
                    $backups[] = [
                        'name' => $file->getFilename(),
                        'size' => $this->formatSize($file->getSize()),
                        'date' => date('Y-m-d H:i:s', $file->getMTime()),
                        'path' => $file->getPathname(),
                    ];
                }
            }
        }

        // Sort by date descending
        usort($backups, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return view('admin.backups.index', compact('backups'));
    }

    /**
     * Create a new backup
     */
    public function create(Request $request)
    {
        try {
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port') ?: 3306;

            $backupPath = storage_path('app/backups');
            if (!File::isDirectory($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }

            $filename = 'backup_' . date('Y-m-d_His') . '.sql';
            $filepath = $backupPath . '/' . $filename;

            // Use mysqldump
            $command = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($filepath)
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                // Fallback: use PHP-based backup
                $this->backupWithPHP($filepath);
            }

            return redirect()->route('admin.backups.index')
                ->with('success', "Backup berhasil dibuat: {$filename}");
        } catch (\Exception $e) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    /**
     * Download a backup file
     */
    public function download($filename)
    {
        $backupPath = storage_path('app/backups');
        $filepath = $backupPath . '/' . basename($filename);

        if (!File::exists($filepath)) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'File backup tidak ditemukan.');
        }

        return Response::download($filepath);
    }

    /**
     * Delete a backup file
     */
    public function destroy($filename)
    {
        $backupPath = storage_path('app/backups');
        $filepath = $backupPath . '/' . basename($filename);

        if (File::exists($filepath)) {
            File::delete($filepath);
            return redirect()->route('admin.backups.index')
                ->with('success', 'Backup berhasil dihapus.');
        }

        return redirect()->route('admin.backups.index')
            ->with('error', 'File backup tidak ditemukan.');
    }

    /**
     * Restore database from backup
     */
    public function restore(Request $request, $filename)
    {
        try {
            $backupPath = storage_path('app/backups');
            $filepath = $backupPath . '/' . basename($filename);

            if (!File::exists($filepath)) {
                return redirect()->route('admin.backups.index')
                    ->with('error', 'File backup tidak ditemukan.');
            }

            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            $port = config('database.connections.mysql.port') ?: 3306;

            $command = sprintf(
                'mysql --host=%s --port=%s --user=%s --password=%s %s < %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($filepath)
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                return redirect()->route('admin.backups.index')
                    ->with('error', 'Gagal restore backup. Pastikan MySQL client terinstall.');
            }

            return redirect()->route('admin.backups.index')
                ->with('success', "Database berhasil di-restore dari: {$filename}");
        } catch (\Exception $e) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'Gagal restore backup: ' . $e->getMessage());
        }
    }

    /**
     * PHP-based backup fallback
     */
    private function backupWithPHP($filepath)
    {
        $tables = DB::select('SHOW TABLES');
        $output = "-- Database Backup\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Database: " . config('database.connections.mysql.database') . "\n\n";
        $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $database = config('database.connections.mysql.database');

        foreach ($tables as $table) {
            $tableName = $table->{'Tables_in_' . $database};

            // Get table structure
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $create = $createTable[0]->{'Create Table'};
            $output .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $output .= $create . ";\n\n";

            // Get table data
            $rows = DB::select("SELECT * FROM `{$tableName}`");
            if (count($rows) > 0) {
                $columns = array_keys((array) $rows[0]);
                $columnList = implode('`, `', $columns);

                foreach (array_chunk($rows, 500) as $chunk) {
                    $output .= "INSERT INTO `{$tableName}` (`{$columnList}`) VALUES\n";
                    $values = [];
                    foreach ($chunk as $row) {
                        $rowValues = [];
                        foreach ((array) $row as $value) {
                            $rowValues[] = is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                        }
                        $values[] = '(' . implode(', ', $rowValues) . ')';
                    }
                    $output .= implode(",\n", $values) . ";\n\n";
                }
            }
        }

        $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
        File::put($filepath, $output);
    }

    /**
     * Format file size
     */
    private function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
