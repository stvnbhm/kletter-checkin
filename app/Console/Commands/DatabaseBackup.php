<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseBackup extends Command
{
    protected $signature = 'backup:database';
    protected $description = 'MySQL Datenbank-Backup erstellen';

    public function handle(): void
    {
        $backupDir = storage_path('backups');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $date     = now()->format('Y-m-d_H-i-s');
        $filename = "kletterdom_{$date}.sql.gz";
        $path     = "{$backupDir}/{$filename}";

        $db       = config('database.connections.mysql.database');
        $user     = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host     = config('database.connections.mysql.host');

        $command = "mysqldump -h {$host} -u {$user} -p{$password} {$db} | gzip > {$path}";
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error("Backup fehlgeschlagen!");
            \Log::error("DB Backup fehlgeschlagen", ['exit' => $exitCode]);
            return;
        }

        // Backups älter als 30 Tage löschen
        collect(glob("{$backupDir}/*.sql.gz"))
            ->filter(fn($f) => filemtime($f) < now()->subDays(30)->timestamp)
            ->each(fn($f) => unlink($f));

        $this->info("✅ Backup gespeichert: {$filename}");
        \Log::info("DB Backup erfolgreich", ['file' => $filename]);
    }
}