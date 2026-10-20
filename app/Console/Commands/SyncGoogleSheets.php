<?php

namespace App\Console\Commands;

use App\Services\GoogleSheetsSyncService;
use Illuminate\Console\Command;

class SyncGoogleSheets extends Command
{
    protected $signature = 'google:sheets:inbound';
    protected $description = 'Sinkronisasi inbound data formulir Google Forms.';

    public function handle(): int
    {
        $service = new GoogleSheetsSyncService();
        try {
            $result = $service->sync();
            $this->info("Sync inbound completed: {$result['added']} added, {$result['updated']} updated, {$result['skipped']} skipped.");
        } catch (\Throwable $e) {
            $this->error('Sync inbound failed: ' . $e->getMessage());
            return 1;
        }
        return 0;
    }
}
