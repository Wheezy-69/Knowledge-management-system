<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('tags/Index', ['tags' => Tag::query()->withCount('problems')->orderBy('name')->paginate(30)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Tag::query()->create($data);

        return back()->with('success', 'Tag created.');
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $data = $this->validated($request, $tag);
        DB::transaction(function () use ($tag, $data): void {
            $key = $tag->key;
            foreach ($tag->problems()->get() as $problem) {
                $problem->update(['tags' => array_map(fn (string $name) => Tag::keyFor($name) === $key ? $data['name'] : $name, $problem->tags ?? [])]);
            }
            $tag->update($data);
        });

        return back()->with('success', 'Tag updated everywhere.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        DB::transaction(function () use ($tag): void {
            foreach ($tag->problems()->get() as $problem) {
                $problem->update(['tags' => array_values(array_filter($problem->tags ?? [], fn (string $name) => Tag::keyFor($name) !== $tag->key))]);
            }
            $tag->delete();
        });

        return back()->with('success', 'Tag removed; problems were preserved.');
    }

    /** @return array{name: string, key: string} */
    private function validated(Request $request, ?Tag $tag = null): array
    {
        $request->validate(['name' => ['required', 'string', 'max:40']]);
        $name = trim(preg_replace('/\s+/u', ' ', $request->string('name')->toString()) ?? '');
        $request->merge(['name' => $name, 'key' => Tag::keyFor($name)]);

        return $request->validate(['name' => ['required', 'string', 'max:40'], 'key' => ['required', 'string', 'max:40', Rule::unique('tags', 'key')->ignore($tag?->id)]]);
    }
}
