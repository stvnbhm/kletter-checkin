<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportMembers extends Command
{
    protected $signature = 'members:import {csvfile}';
    protected $description = 'Importiert Mitglieder aus CSV (member_number,first_name,last_name,email,membership_status,payment_status,birth_date)';

    public function handle()
    {
        $csvPath = $this->argument('csvfile');

        if (!file_exists($csvPath)) {
            $this->error('CSV-Datei nicht gefunden: ' . $csvPath);
            return self::FAILURE;
        }

        $handle = fopen($csvPath, 'r');

        if (!$handle) {
            $this->error('CSV-Datei konnte nicht geöffnet werden.');
            return self::FAILURE;
        }

        $header = fgetcsv($handle);

        if (!$header) {
            $this->error('CSV-Datei ist leer.');
            fclose($handle);
            return self::FAILURE;
        }

        $expectedHeader = [
            'member_number',
            'first_name',
            'last_name',
            'email',
            'membership_status',
            'payment_status',
            'birth_date',
        ];

        if ($header !== $expectedHeader) {
            $this->error('Ungültiger CSV-Header.');
            $this->line('Erwartet: ' . implode(',', $expectedHeader));
            $this->line('Gefunden: ' . implode(',', $header));
            fclose($handle);
            return self::FAILURE;
        }

        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 7 || empty(trim($row[0]))) {
                $skipped++;
                continue;
            }

            $data = [
                'member_number' => trim($row[0]),
                'first_name' => trim($row[1]),
                'last_name' => trim($row[2]),
                'email' => trim($row[3]) ?: null,
                'membership_status' => trim($row[4]) ?: 'active',
                'payment_status' => trim($row[5]) ?: 'paid',
                'birth_date' => trim($row[6]) ?: null,
                'last_imported_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('members')->updateOrInsert(
                ['member_number' => $data['member_number']],
                array_merge($data, [
                    'created_at' => now(),
                ])
            );

            $imported++;
        }

        fclose($handle);

        $this->info("Importiert/Aktualisiert: {$imported}");
        $this->info("Übersprungen: {$skipped}");

        Log::info('Members import finished', [
            'imported' => $imported,
            'skipped' => $skipped,
            'file' => $csvPath,
        ]);

        return self::SUCCESS;
    }
}