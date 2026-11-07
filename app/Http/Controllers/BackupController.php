<?php

namespace App\Http\Controllers;

use App\Services\KnowledgeBackup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as DownloadResponse;

class BackupController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('backups/Index');
    }

    public function export(KnowledgeBackup $backup): DownloadResponse
    {
        $json = $backup->export();
        $backup->inspect($json);

        return response($json, 200, ['Content-Type' => 'application/json', 'Content-Disposition' => 'attachment; filename="knowledge-vault-'.now()->format('Ymd-His').'.json"', 'Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function store(Request $request, KnowledgeBackup $backup): RedirectResponse
    {
        $request->validate(['backup' => ['required', 'file', 'max:20480'], 'confirm' => ['accepted']]);
        $file = $request->file('backup');
        abort_unless($file instanceof UploadedFile, 422);
        $counts = $backup->restore($file->getContent());

        return back()->with('success', 'Backup restored as new copies: '.$counts['vaults'].' vaults, '.$counts['problems'].' problems.');
    }
}
