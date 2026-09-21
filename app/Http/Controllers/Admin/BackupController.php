<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $backupPath = storage_path('app/backups');
        $backups = [];

        if (File::isDirectory($backupPath)) {
            $files = File::files($backupPath);
            foreach ($files as $file) {
                $backups[] = [
                    'name' => $file->getFilename(),
                    'size' => $this->formatBytes($file->getSize()),
                    'date' => $file->getMTime(),
                ];
            }
            usort($backups, fn($a, $b) => $b['date'] <=> $a['date']);
        }

        return view('admin.backup.index', compact('backups'));
    }

    public function create(): \Illuminate\Http\RedirectResponse
    {
        $backupPath = storage_path('app/backups');
        File::makeDirectory($backupPath, 0755, true, true);

        $filename = 'backup-' . now()->format('Y-m-d-His') . '.sql';
        $filepath = $backupPath . '/' . $filename;

        $driver = DB::getDriverName();

        $sql = "-- FurShield Database Backup\n";
        $sql .= "-- Date: " . now()->format('Y-m-d H:i:s') . "\n";
        $sql .= "-- Driver: {$driver}\n\n";

        if (in_array($driver, ['mysql', 'mariadb'])) {
            $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
            $sql .= "SET NAMES utf8mb4;\n\n";
        } elseif ($driver === 'pgsql') {
            $sql .= "SET client_encoding = 'UTF8';\n\n";
        }

        $tables = match ($driver) {
            'mysql', 'mariadb' => array_map(
                fn($row) => array_values((array) $row)[0],
                DB::select('SHOW TABLES')
            ),
            'pgsql' => array_map(
                fn($row) => $row->tablename,
                DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'")
            ),
            'sqlite' => array_map(
                fn($row) => $row->name,
                DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")
            ),
            default => [],
        };

        foreach ($tables as $tableName) {
            $sql .= "-- {$tableName}\n";

            $createTable = $this->getCreateTableStatement($tableName, $driver);
            if ($createTable) {
                $sql .= $createTable . ";\n\n";
            }

            $rows = DB::table($tableName)->get();
            if ($rows->isEmpty()) {
                $sql .= "\n";
                continue;
            }

            foreach ($rows as $row) {
                $rowArray = (array) $row;
                $columns = match ($driver) {
                    'pgsql' => array_map(fn($k) => "\"{$k}\"", array_keys($rowArray)),
                    default => array_map(fn($k) => "`{$k}`", array_keys($rowArray)),
                };
                $values = array_map(function ($v) use ($driver) {
                    if ($v === null) return 'NULL';
                    if (is_int($v) || is_float($v)) return $v;
                    $escaped = $driver === 'pgsql'
                        ? str_replace(["'", "\\'"], ["'", "\\'"], $v)
                        : addslashes($v);
                    return "'" . $escaped . "'";
                }, array_values($rowArray));

                $sql .= "INSERT INTO `{$tableName}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }

        if (in_array($driver, ['mysql', 'mariadb'])) {
            $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        }

        File::put($filepath, $sql);

        if (!File::exists($filepath) || File::size($filepath) === 0) {
            return back()->with('error', 'Backup failed: file was not created.');
        }

        return back()->with('success', "Backup created: {$filename}");
    }

    private function getCreateTableStatement(string $tableName, string $driver): ?string
    {
        try {
            $result = match ($driver) {
                'mysql', 'mariadb' => DB::select("SHOW CREATE TABLE `{$tableName}`"),
                'pgsql' => null,
                'sqlite' => DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$tableName]),
                default => null,
            };

            if ($result === null) return null;

            if ($driver === 'mysql' || $driver === 'mariadb') {
                return $result[0]->{'Create Table'} ?? null;
            }

            if ($driver === 'sqlite') {
                return $result[0]->sql ?? null;
            }

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function download(string $filename): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filepath = storage_path("app/backups/{$filename}");

        if (!File::exists($filepath)) {
            abort(404);
        }

        return response()->download($filepath, $filename);
    }

    public function destroy(string $filename): \Illuminate\Http\RedirectResponse
    {
        $filepath = storage_path("app/backups/{$filename}");

        if (File::exists($filepath)) {
            File::delete($filepath);
        }

        return back()->with('success', 'Backup deleted successfully.');
    }

    private function formatBytes(int $bytes): string
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
