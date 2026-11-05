<?php

namespace App\Http\Controllers;

use App\Http\Requests\VaultRequest;
use App\Models\Vault;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VaultController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('vaults/Index', [
            'vaults' => Vault::query()->withCount('sections')->latest()->paginate(12),
        ]);
    }

    public function store(VaultRequest $request): RedirectResponse
    {
        $vault = new Vault($request->safe()->only(['name', 'description']));
        if ($cover = $request->file('cover')) {
            $vault->cover_path = $this->storeCover($cover);
        }
        $vault->save();

        return to_route('vaults.show', $vault)->with('success', 'Vault created.');
    }

    public function show(Vault $vault): Response
    {
        return Inertia::render('vaults/Show', [
            'vault' => $vault,
            'sections' => $vault->sections()->withCount('problems')->orderBy('name')->paginate(12),
        ]);
    }

    public function update(VaultRequest $request, Vault $vault): RedirectResponse
    {
        $oldCover = $vault->cover_path;
        $vault->fill($request->safe()->only(['name', 'description']));
        if ($request->boolean('remove_cover')) {
            $vault->cover_path = null;
        }
        if ($cover = $request->file('cover')) {
            $vault->cover_path = $this->storeCover($cover);
        }
        $vault->save();
        if ($oldCover && $oldCover !== $vault->cover_path) {
            Storage::disk('local')->delete($oldCover);
        }

        return back()->with('success', 'Vault updated.');
    }

    public function destroy(Vault $vault): RedirectResponse
    {
        $cover = $vault->cover_path;
        $vault->delete();
        if ($cover) {
            Storage::disk('local')->delete($cover);
        }

        return to_route('vaults.index')->with('success', 'Vault and its contents deleted.');
    }

    public function cover(Vault $vault): StreamedResponse
    {
        abort_unless($vault->cover_path && Storage::disk('local')->exists($vault->cover_path), 404);

        return Storage::disk('local')->response($vault->cover_path, null, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function storeCover(UploadedFile $cover): string
    {
        $path = $cover->store('vault-covers', 'local');
        if ($path === false) {
            throw ValidationException::withMessages(['cover' => 'The cover could not be saved. Please try again.']);
        }

        return $path;
    }
}
