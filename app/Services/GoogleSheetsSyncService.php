<?php

namespace App\Services;

use App\Models\Visit;
use Google\Client;
use Google\Service\Exception as GoogleServiceException;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class GoogleSheetsSyncService
{
    private const HEADERS = [
        'sheet_row_id',
        'registration_number',
        'source_timestamp',
        'applicant_name',
        'institution',
        'phone',
        'email',
        'participants_count',
        'purpose',
        'facility_target',
        'visit_date',
        'scheduled_date',
        'visit_time',
        'status',
        'whatsapp_status',
        'last_synced_at',
    ];

    private Sheets $sheets;

    private string $spreadsheetId;

    public function __construct()
    {
        $config = config('services.google_sheets');

        if (! ($config['enabled'] ?? false)) {
            throw new RuntimeException('Integrasi Google Sheets belum diaktifkan.');
        }

        $this->spreadsheetId = $this->normalizeSpreadsheetId($config['spreadsheet_id'] ?? null);
        $credentialsPath = (string) ($config['credentials'] ?? $config['credentials_path'] ?? '');

        if ($this->spreadsheetId === '') {
            throw new RuntimeException('Spreadsheet ID Google Sheets belum dikonfigurasi.');
        }

        if ($credentialsPath === '' || ! is_file($credentialsPath) || ! is_readable($credentialsPath)) {
            throw new RuntimeException('File kredensial Google service account tidak tersedia atau tidak dapat dibaca.');
        }

        try {
            $client = new Client();
            $client->setAuthConfig($credentialsPath);
            $client->setScopes([Sheets::SPREADSHEETS]);

            $this->sheets = new Sheets($client);
        } catch (Throwable $exception) {
            throw new RuntimeException('Kredensial Google service account tidak valid.', previous: $exception);
        }
    }

    /**
     * @return array{added: int, updated: int, skipped: int, duplicates: array<int, string>}
     */
    public function sync(): array
    {
        try {
            // 1. Verifikasi tab di spreadsheet
            $spreadsheet = $this->sheets->spreadsheets->get($this->spreadsheetId, [
                'fields' => 'sheets.properties.title',
            ]);

            $sheetTitles = [];
            foreach ($spreadsheet->getSheets() as $s) {
                $sheetTitles[] = $s->getProperties()->getTitle();
            }

            if (! in_array('Form Responses 1', $sheetTitles, true)) {
                throw new RuntimeException("Tab sumber 'Form Responses 1' tidak ditemukan di spreadsheet.");
            }

            if (! in_array('Kunjungan', $sheetTitles, true)) {
                $this->createIntegrationTab();
            } else {
                $this->ensureIntegrationHeader();
            }

            // 2. Baca Form Responses 1
            $response = $this->sheets->spreadsheets_values->get(
                $this->spreadsheetId,
                'Form Responses 1!A:F'
            );
            $formRows = $response->getValues() ?? [];
        } catch (GoogleServiceException $exception) {
            throw $this->googleException($exception);
        } catch (Throwable $exception) {
            throw new RuntimeException('Google Sheets tidak dapat diakses: ' . $exception->getMessage(), previous: $exception);
        }

        if (count($formRows) <= 1) {
            return ['added' => 0, 'updated' => 0, 'skipped' => 0, 'duplicates' => []];
        }

        $dataRows = array_slice($formRows, 1);

        $added = 0;
        $updated = 0;
        $skipped = 0;
        $duplicates = [];
        $seenTimestamps = [];

        foreach ($dataRows as $row) {
            $timestamp = trim((string) ($row[0] ?? ''));
            $applicantName = trim((string) ($row[1] ?? ''));
            $institution = trim((string) ($row[2] ?? ''));
            $phone = trim((string) ($row[3] ?? ''));
            $participantsCount = trim((string) ($row[4] ?? '0'));
            $visitDateStr = trim((string) ($row[5] ?? ''));

            if ($timestamp === '') {
                $skipped++;
                continue;
            }

            if (isset($seenTimestamps[$timestamp])) {
                $duplicates[] = $timestamp;
                $skipped++;
                continue;
            }
            $seenTimestamps[$timestamp] = true;

            // sheet_row_id deterministik dari spreadsheetId + timestamp
            $sheetRowId = 'BRIN-' . substr(md5($this->spreadsheetId . '-' . $timestamp), 0, 12);

            // Parsing tanggal (DD/MM/YYYY atau YYYY-MM-DD)
            $parsedVisitDate = null;
            if ($visitDateStr !== '') {
                if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $visitDateStr)) {
                    $parts = explode('/', $visitDateStr);
                    $parsedVisitDate = "{$parts[2]}-{$parts[1]}-{$parts[0]}";
                } else {
                    $parsedVisitDate = $visitDateStr;
                }
            }

            $visit = Visit::where('sheet_row_id', $sheetRowId)->first();

            if (! $visit) {
                Visit::create([
                    'sheet_row_id' => $sheetRowId,
                    'registration_number' => 'BRIN-' . strtoupper(Str::random(6)),
                    'applicant_name' => $applicantName ?: 'Tanpa Nama',
                    'institution' => $institution ?: 'Umum',
                    'phone' => $phone ?: '-',
                    'email' => '',
                    'participants_count' => is_numeric($participantsCount) ? (int) $participantsCount : 1,
                    'purpose' => 'Kunjungan Riset / Edukasi',
                    'facility_target' => 'Kawasan Sains dan Teknologi',
                    'visit_date' => $parsedVisitDate ?: now()->format('Y-m-d'),
                    'status' => Visit::STATUS_MENUNGGU,
                    'whatsapp_status' => Visit::WA_BELUM,
                    'last_synced_at' => now(),
                ]);
                $added++;
            } else {
                // Update basic form fields, lindungi field operasional admin
                $visit->update([
                    'applicant_name' => $applicantName ?: $visit->applicant_name,
                    'institution' => $institution ?: $visit->institution,
                    'phone' => $phone ?: $visit->phone,
                    'participants_count' => is_numeric($participantsCount) ? (int) $participantsCount : $visit->participants_count,
                    'visit_date' => $parsedVisitDate ?: $visit->visit_date,
                    'last_synced_at' => now(),
                ]);
                $updated++;
            }
        }

        // 3. Outbound sync: Tulis seluruh data ke tab 'Kunjungan'
        $this->syncOutboundToIntegrationTab();

        return [
            'added' => $added,
            'updated' => $updated,
            'skipped' => $skipped,
            'duplicates' => array_values(array_unique($duplicates)),
        ];
    }

    private function createIntegrationTab(): void
    {
        $request = new Sheets\BatchUpdateSpreadsheetRequest([
            'requests' => [
                ['addSheet' => ['properties' => ['title' => 'Kunjungan']]]
            ]
        ]);
        $this->sheets->spreadsheets->batchUpdate($this->spreadsheetId, $request);
        $this->writeHeader();
    }

    private function ensureIntegrationHeader(): void
    {
        try {
            $response = $this->sheets->spreadsheets_values->get(
                $this->spreadsheetId,
                'Kunjungan!A1:P1'
            );
            $values = $response->getValues() ?? [];
            if ($values === [] || ($values[0] ?? []) === []) {
                $this->writeHeader();
            }
        } catch (Throwable) {
            $this->writeHeader();
        }
    }

    private function writeHeader(): void
    {
        $this->sheets->spreadsheets_values->update(
            $this->spreadsheetId,
            'Kunjungan!A1:P1',
            new ValueRange(['values' => [self::HEADERS]]),
            ['valueInputOption' => 'RAW']
        );
    }


    public function syncOutboundToIntegrationTab(): void
    {
        $visits = Visit::query()->orderBy('id')->get();
        $rows = [self::HEADERS];
        foreach ($visits as $visit) {
            $rows[] = [
                $visit->sheet_row_id ?? ('LOCAL-' . $visit->id),
                $visit->registration_number,
                $visit->created_at?->format('Y-m-d H:i:s') ?? '',
                $visit->applicant_name,
                $visit->institution,
                $visit->phone,
                $visit->email,
                $visit->participants_count,
                $visit->purpose,
                $visit->facility_target,
                $visit->visit_date?->format('Y-m-d') ?? '',
                $visit->scheduled_date?->format('Y-m-d') ?? '',
                $visit->visit_time ?? '',
                $visit->status,
                $visit->whatsapp_status,
                $visit->last_synced_at?->format('Y-m-d H:i:s') ?? '',
            ];
        }
        $this->sheets->spreadsheets_values->clear(
            $this->spreadsheetId,
            'Kunjungan!A:P',
            new Sheets\ClearValuesRequest()
        );
        if (count($rows) > 0) {
            $this->sheets->spreadsheets_values->update(
                $this->spreadsheetId,
                'Kunjungan!A1:P' . count($rows),
                new ValueRange(['values' => $rows]),
                ['valueInputOption' => 'RAW']
            );
        }
    }

    private function normalizeSpreadsheetId(mixed $value): string
    {
        $value = trim((string) $value);
        if (preg_match('#^https://docs\.google\.com/spreadsheets/d/([a-zA-Z0-9_-]+)(?:/|$)#', $value, $matches)) {
            return $matches[1];
        }
        return preg_match('/^[a-zA-Z0-9_-]+$/', $value) ? $value : '';
    }

    private function googleException(GoogleServiceException $exception): RuntimeException
    {
        return match ($exception->getCode()) {
            401 => new RuntimeException('Autentikasi Google service account gagal.', previous: $exception),
            403 => new RuntimeException('Akses ditolak oleh Google Sheets (403 PERMISSION_DENIED). Berikan peran Editor kepada service account.', previous: $exception),
            404 => new RuntimeException('Spreadsheet atau tab Google Sheets tidak ditemukan.', previous: $exception),
            429 => new RuntimeException('Kuota Google Sheets sedang habis.', previous: $exception),
            default => new RuntimeException('Google Sheets API menolak permintaan: ' . $exception->getMessage(), previous: $exception),
        };
    }
}
