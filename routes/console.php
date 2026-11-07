<?php

use App\Services\KnowledgeBackup;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Artisan::command('vault:backup', function (KnowledgeBackup $backup): void {
    $json = $backup->export();
    $backup->inspect($json);
    $path = 'backups/knowledge-vault-'.now()->format('Ymd-His').'-'.Str::uuid().'.json';
    if (! Storage::disk('local')->put($path, $json)) {
        throw new RuntimeException('Backup could not be written.');
    }
    $backup->inspect(Storage::disk('local')->get($path));
    $this->info('Verified backup: '.Storage::disk('local')->path($path));
})->purpose('Create and validate a non-destructive portable Knowledge Vault backup');

use Illuminate\Foundation\Inspiring;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
