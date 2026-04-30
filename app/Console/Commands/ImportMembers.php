<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportMembers extends Command
{
    protected $signature = 'members:import {csvfile}';
    protected $description = 'Importiert Mitglieder aus CSV (Semikolon-getrennt, österreichisches Format)';

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

        // BOM entfernen
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Semikolon als Trennzeichen
        $header = fgetcsv($handle, 0, ';');

        if (!$header) {
            $this->error('CSV-Datei ist leer.');
            fclose($handle);
            return self::FAILURE;
        }

        // BOM aus erstem Header-Wert entfernen
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $header = array_map('trim', $header);

        // Pflicht-Spalten prüfen
        $required = ['Mitgliedsnummer', 'Vorname', 'Nachname', 'Email', 'Status', 'Betrag offen', 'Geburtsdatum'];
        $missing = array_diff($required, $header);

        if (!empty($missing)) {
            $this->error('Fehlende Spalten: ' . implode(', ', $missing));
            $this->line('Gefundene Spalten: ' . implode(', ', $header));
            fclose($handle);
            return self::FAILURE;
        }

        // Spalten-Indizes dynamisch ermitteln
        $col = array_flip($header);

        $imported = 0;
        $skipped  = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) < count($header)) {
                $skipped++;
                continue;
            }

            $memberNumber = trim($row[$col['Mitgliedsnummer']] ?? '');
            if (empty($memberNumber)) {
                $skipped++;
                continue;
            }

            // Mitgliedsstatus
            $statusRaw        = strtolower(trim($row[$col['Status']] ?? ''));
            $membershipStatus = match (true) {
                str_contains($statusRaw, 'ausgetreten') => 'inactive',
                str_contains($statusRaw, 'inaktiv')     => 'inactive',
                str_contains($statusRaw, 'gelöscht')    => 'inactive',  // ← NEU
                str_contains($statusRaw, 'geloescht')   => 'inactive',  // ← NEU (Fallback ohne Umlaut)
                default                                 => 'active',
            };

            // Beitragsstatus aus "Betrag offen"
            $betragOffen   = floatval(trim($row[$col['Betrag offen']] ?? '0'));
            $paymentStatus = $betragOffen > 0 ? 'open' : 'paid';

            // Geburtsdatum: TT.MM.JJJJ → JJJJ-MM-TT
            $birthDateRaw = trim($row[$col['Geburtsdatum']] ?? '');
            $birthDate    = $this->parseBirthDate($birthDateRaw);

            // E-Mail bereinigen
            $email = trim($row[$col['Email']] ?? '') ?: null;

            $data = [
                'member_number'     => $memberNumber,
                'first_name'        => trim($row[$col['Vorname']] ?? ''),
                'last_name'         => trim($row[$col['Nachname']] ?? ''),
                'email'             => $email,
                'membership_status' => $membershipStatus,
                'payment_status'    => $paymentStatus,
                'birth_date'        => $birthDate,
                'last_imported_at'  => now(),
                'updated_at'        => now(),
            ];

            DB::table('members')->updateOrInsert(
                ['member_number' => $data['member_number']],
                array_merge($data, ['created_at' => now()])
            );

            $imported++;
        }

        fclose($handle);

        $this->info("Importiert/Aktualisiert: {$imported}");
        $this->info("Übersprungen: {$skipped}");

        Log::info('Members import finished', [
            'imported' => $imported,
            'skipped'  => $skipped,
            'file'     => $csvPath,
        ]);

        return self::SUCCESS;
    }

    private function parseBirthDate(string $value): ?string
    {
        $value = trim($value);
        if (empty($value)) return null;

        // TT.MM.JJJJ
        if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $value, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }

        // Bereits JJJJ-MM-TT
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        return null;
    }
}