<?php

use App\Models\Attempt;
use App\Models\ContentBlock;
use App\Models\Problem;
use App\Models\Tag;
use App\Models\Vault;
use App\Services\KnowledgeBackup;
use App\Services\ProblemTags;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

function expandedProblem(): Problem
{
    return Problem::factory()->create(['title' => 'Unicode résumé 日本語', 'description' => 'Description', 'corrected_solution' => "    fixed();\n", 'explanation' => 'A useful explanation.']);
}
function problemArgs(Problem $problem): array
{
    return [$problem->section->vault, $problem->section, $problem];
}
function uploadArchive(string $json): UploadedFile
{
    return UploadedFile::fake()->createWithContent('backup.json', $json);
}
function signedArchive(array $archive): string
{
    $archive['sha256'] = hash('sha256', json_encode($archive['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    $archive['counts'] = array_map(count(...), $archive['data']);

    return json_encode($archive, JSON_THROW_ON_ERROR);
}

test('tag names are normalized and reusable assignments support rename and deletion', function () {
    $this->post(route('tags.store'), ['name' => '  PHP  '])->assertRedirect()->assertSessionHasNoErrors();
    $tag = Tag::query()->sole();
    $this->post(route('tags.store'), ['name' => 'php'])->assertSessionHasErrors('key');
    $problem = expandedProblem();
    app(ProblemTags::class)->sync($problem, ['PHP']);
    $this->put(route('tags.update', $tag), ['name' => 'Laravel'])->assertRedirect();
    expect($problem->fresh()->tags)->toBe(['Laravel']);
    expect($problem->tagRecords()->sole()->id)->toBe($tag->id);
    $this->get(route('tags.index'))->assertOk()->assertInertia(fn (Assert $page) => $page->where('tags.data.0.problems_count', 1));
    $this->delete(route('tags.destroy', $tag))->assertRedirect();
    expect($problem->fresh()->tags)->toBe([]);
    $this->assertModelExists($problem);
    $this->assertDatabaseCount('problem_tag', 0);
});

test('editing a problem assigns and removes catalog tags', function () {
    $problem = expandedProblem();
    $this->put(route('vaults.sections.problems.update', problemArgs($problem)), ['title' => 'Tagged', 'programming_language' => 'php', 'tags' => ['arrays', 'debugging']])->assertRedirect();
    expect($problem->tagRecords()->count())->toBe(2);
    $this->put(route('vaults.sections.problems.update', problemArgs($problem)), ['title' => 'Tagged', 'programming_language' => 'php', 'tags' => []])->assertRedirect();
    expect($problem->tagRecords()->count())->toBe(0);
});

test('search finds explanations solutions attempts notes and blocks with small previews', function (string $field) {
    $problem = Problem::factory()->create(['title' => 'Ordinary title', 'description' => null, 'corrected_solution' => null, 'explanation' => null]);
    $text = str_repeat('prefix ', 100).'needle42'.str_repeat(' tail', 100);
    if (in_array($field, ['explanation', 'corrected_solution'])) {
        $problem->update([$field => $text]);
    } elseif ($field === 'block') {
        $problem->blocks()->create(['type' => 'text', 'content' => $text, 'position' => 1]);
    } else {
        Attempt::factory()->for($problem)->create(['code' => $field === 'code' ? $text : 'other', 'notes' => $field === 'notes' ? $text : null]);
    }
    $this->get(route('problems.index', ['search' => 'needle42']))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->has('problems.data', 1)->where('problems.data.0.preview.text', fn (string $preview) => str_contains($preview, 'needle42') && mb_strlen($preview) <= 242)
        ->missing('problems.data.0.corrected_solution')->missing('problems.data.0.explanation')->missing('problems.data.0.matched_attempt'));
})->with(['explanation', 'corrected_solution', 'code', 'notes', 'block']);

test('search combines vault section language and all selected tags and paginates', function () {
    $problem = Problem::factory()->create(['title' => 'Matching', 'programming_language' => 'php']);
    app(ProblemTags::class)->sync($problem, ['one', 'two']);
    $tagIds = $problem->tagRecords()->pluck('tags.id')->all();
    Problem::factory()->count(15)->for($problem->section)->create(['title' => 'Matching', 'programming_language' => 'php'])->each(fn (Problem $p) => app(ProblemTags::class)->sync($p, ['one', 'two']));
    Problem::factory()->for($problem->section)->create(['title' => 'Matching', 'programming_language' => 'python']);
    Problem::factory()->create(['title' => 'Matching', 'programming_language' => 'php']);
    $filters = ['search' => 'Matching', 'vault_id' => $problem->section->vault_id, 'section_id' => $problem->section_id, 'language' => 'php', 'tag_ids' => $tagIds, 'page' => 2];
    $this->get(route('problems.index', $filters))->assertOk()->assertInertia(fn (Assert $page) => $page->where('problems.total', 16)->has('problems.data', 1)->where('filters.tag_ids', $tagIds));
    $this->get(route('problems.index', ['search' => 'absent']))->assertInertia(fn (Assert $page) => $page->has('problems.data', 0));
    $this->get(route('vaults.sections.show', [$problem->section->vault, $problem->section, 'tag_ids' => $tagIds]))->assertOk()->assertInertia(fn (Assert $page) => $page->where('problems.total', 16));
});

test('invalid filters are rejected without exposing unrelated hierarchy', function () {
    $this->from(route('problems.index'))->get(route('problems.index', ['tag_ids' => [999999]]))->assertSessionHasErrors('tag_ids.0');
    $problem = expandedProblem();
    $other = Vault::factory()->create();
    $this->get(route('vaults.sections.show', [$other, $problem->section, 'search' => 'Description']))->assertNotFound();
});

test('related links are reciprocal unique reject self links and cascade safely', function () {
    $a = expandedProblem();
    $b = expandedProblem();
    $this->post(route('vaults.sections.problems.related.store', problemArgs($a)), ['related_problem_id' => $b->id])->assertRedirect();
    $this->get(route('vaults.sections.problems.show', problemArgs($b)))->assertInertia(fn (Assert $page) => $page->has('related', 1)->where('related.0.id', $a->id));
    $this->post(route('vaults.sections.problems.related.store', problemArgs($b)), ['related_problem_id' => $a->id])->assertSessionHasErrors('related_problem_id');
    $this->post(route('vaults.sections.problems.related.store', problemArgs($a)), ['related_problem_id' => $a->id])->assertSessionHasErrors('related_problem_id');
    $this->assertDatabaseCount('problem_links', 1);
    $this->delete(route('vaults.sections.problems.related.destroy', [...problemArgs($a), $b->id]))->assertRedirect();
    $this->assertDatabaseCount('problem_links', 0);
    $this->post(route('vaults.sections.problems.related.store', problemArgs($a)), ['related_problem_id' => $b->id]);
    $this->delete(route('vaults.sections.problems.destroy', problemArgs($b)))->assertRedirect();
    $this->assertDatabaseCount('problem_links', 0);
    $this->assertModelExists($a);
});

test('block CRUD ordering exact code and legacy conversion preserve originals', function () {
    $problem = expandedProblem();
    $args = problemArgs($problem);
    $original = $problem->getAttributes();
    $code = "    echo '日本語';\n\n";
    $this->post(route('vaults.sections.problems.blocks.store', $args), ['type' => 'code', 'content' => $code, 'language' => 'php', 'position' => 4])->assertRedirect();
    $block = ContentBlock::query()->sole();
    expect($block->content)->toBe($code);
    $this->put(route('vaults.sections.problems.blocks.update', [...$args, $block]), ['type' => 'text', 'content' => 'Changed', 'position' => 5])->assertRedirect();
    $this->post(route('vaults.sections.problems.blocks.convert', $args))->assertRedirect();
    $this->post(route('vaults.sections.problems.blocks.convert', $args))->assertRedirect();
    expect($problem->blocks()->count())->toBe(4);
    expect($problem->blocks()->where('source', 'corrected_solution')->sole()->content)->toBe($original['corrected_solution']);
    $this->get(route('vaults.sections.problems.show', $args))->assertInertia(fn (Assert $page) => $page->where('problem.blocks.0.position', 5)->has('problem.blocks', 4));
    $this->delete(route('vaults.sections.problems.blocks.destroy', [...$args, $block]))->assertRedirect();
    expect($problem->fresh()->corrected_solution)->toBe($original['corrected_solution']);
    expect($problem->fresh()->description)->toBe($original['description']);
});

test('blocks reject invalid types duplicate order and wrong parents', function () {
    $problem = expandedProblem();
    $block = $problem->blocks()->create(['type' => 'text', 'content' => 'Keep', 'position' => 1]);
    $this->post(route('vaults.sections.problems.blocks.store', problemArgs($problem)), ['type' => 'html', 'content' => 'bad', 'position' => 2])->assertSessionHasErrors('type');
    $this->post(route('vaults.sections.problems.blocks.store', problemArgs($problem)), ['type' => 'text', 'content' => 'bad', 'position' => 1])->assertSessionHasErrors('position');
    $other = expandedProblem();
    $this->put(route('vaults.sections.problems.blocks.update', [...problemArgs($other), $block]), ['type' => 'text', 'content' => 'bad', 'position' => 2])->assertNotFound();
    expect($block->fresh()->content)->toBe('Keep');
});

test('comparison accepts only snippets from the selected problem', function () {
    $problem = expandedProblem();
    $attempt = Attempt::factory()->for($problem)->create();
    $other = Attempt::factory()->create();
    $this->get(route('vaults.sections.problems.compare', [...problemArgs($problem), 'left' => (string) $attempt->id, 'right' => 'solution']))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('left', (string) $attempt->id)->where('choices.0.code', $problem->corrected_solution));
    $this->get(route('vaults.sections.problems.compare', [...problemArgs($problem), 'left' => (string) $other->id]))->assertNotFound();
});

test('graph derives tags related edges isolated problems and filtering', function () {
    $a = expandedProblem();
    $b = expandedProblem();
    $isolated = expandedProblem();
    app(ProblemTags::class)->sync($a, ['graph']);
    DB::table('problem_links')->insert(['problem_id' => $a->id, 'related_problem_id' => $b->id]);
    $this->get(route('graph.index'))->assertOk()->assertInertia(fn (Assert $page) => $page->has('nodes', 4)->has('edges', 2)->where('limited', false));
    $this->get(route('graph.index', ['tag_ids' => Tag::query()->pluck('id')->all()]))->assertInertia(fn (Assert $page) => $page->has('nodes', 2)->has('edges', 1));
    $this->assertModelExists($isolated);
});

test('backup round trip preserves unicode whitespace attachments and relationships without overwriting', function () {
    Storage::fake('local');
    $problem = expandedProblem();
    $other = expandedProblem();
    app(ProblemTags::class)->sync($problem, ['日本語', 'PHP']);
    Attempt::factory()->for($problem)->create(['code' => "    sample();\n"]);
    $problem->blocks()->create(['type' => 'code', 'content' => "  block();\n", 'language' => 'php', 'position' => 1]);
    DB::table('problem_links')->insert(['problem_id' => $problem->id, 'related_problem_id' => $other->id]);
    $image = UploadedFile::fake()->image('cover.png')->getContent();
    Storage::disk('local')->put('vault-covers/original.png', $image);
    $problem->section->vault->update(['cover_path' => 'vault-covers/original.png']);
    $backup = app(KnowledgeBackup::class);
    $json = $backup->export();
    $counts = $backup->inspect($json);
    expect($counts['problems'])->toBe(2);
    $this->post(route('backups.store'), ['backup' => uploadArchive($json), 'confirm' => true])->assertRedirect()->assertSessionHasNoErrors();
    $this->assertDatabaseCount('problems', 4);
    $copy = Problem::query()->where('id', '>', $other->id)->where('title', $problem->title)->orderBy('id')->firstOrFail();
    expect($copy->corrected_solution)->toBe($problem->corrected_solution);
    expect($copy->tags)->toBe($problem->fresh()->tags);
    expect($copy->attempts()->sole()->code)->toBe("    sample();\n");
    expect($copy->blocks()->sole()->content)->toBe("  block();\n");
    expect(Storage::disk('local')->get($copy->section->vault->cover_path))->toBe($image);
    $this->assertDatabaseCount('problem_links', 2);
    $this->assertModelExists($problem);
    Storage::disk('local')->assertExists('vault-covers/original.png');
    $this->get(route('backups.export'))->assertOk()->assertHeader('Content-Type', 'application/json');
});

test('backup rejects corrupt truncated unsupported and orphaned data before writing', function () {
    Storage::fake('local');
    $problem = expandedProblem();
    $backup = app(KnowledgeBackup::class);
    $json = $backup->export();
    $archive = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    $invalid = [$json.'broken', str_replace('knowledge-vault', 'other-format', $json)];
    $changed = $archive;
    $changed['data']['problems'][0]['title'] = 'tamper';
    $invalid[] = json_encode($changed);
    $changed = $archive;
    $changed['data']['problems'][0]['section_id'] = 999999;
    $invalid[] = signedArchive($changed);
    foreach ($invalid as $bad) {
        $this->post(route('backups.store'), ['backup' => uploadArchive($bad), 'confirm' => true])->assertSessionHasErrors('backup');
    }
    $this->assertDatabaseCount('problems', 1);
    expect(Storage::disk('local')->allFiles('vault-covers'))->toBeEmpty();
    $this->assertModelExists($problem);
});

test('incomplete covers and missing confirmation cannot restore any rows', function () {
    Storage::fake('local');
    $problem = expandedProblem();
    $archive = json_decode(app(KnowledgeBackup::class)->export(), true, 512, JSON_THROW_ON_ERROR);
    $archive['data']['vaults'][0]['cover'] = ['sha256' => hash('sha256', 'bad'), 'data' => base64_encode('bad')];
    $this->post(route('backups.store'), ['backup' => uploadArchive(signedArchive($archive)), 'confirm' => true])->assertSessionHasErrors('backup');
    $this->post(route('backups.store'), ['backup' => uploadArchive(app(KnowledgeBackup::class)->export())])->assertSessionHasErrors('confirm');
    $this->assertDatabaseCount('problems', 1);
});

test('backup export refuses missing attachments rather than producing incomplete success', function () {
    Storage::fake('local');
    $problem = expandedProblem();
    $problem->section->vault->update(['cover_path' => 'missing.png']);

    $this->from(route('backups.index'))->get(route('backups.export'))->assertSessionHasErrors('backup');
});

test('restore rolls back records and cleans only new attachment paths on storage failure', function () {
    Storage::fake('local');
    $a = expandedProblem();
    $b = expandedProblem();
    $image = UploadedFile::fake()->image('cover.png')->getContent();
    Storage::disk('local')->put('vault-covers/original.png', $image);
    $a->section->vault->update(['cover_path' => 'vault-covers/original.png']);
    $b->section->vault->update(['cover_path' => 'vault-covers/original.png']);
    $json = app(KnowledgeBackup::class)->export();
    $disk = Mockery::mock(FilesystemAdapter::class);
    $disk->shouldReceive('put')->twice()->andReturn(true, false);
    $disk->shouldReceive('delete')->twice()->withArgs(fn (string $path) => str_starts_with($path, 'vault-covers/') && $path !== 'vault-covers/original.png')->andReturn(true);
    Storage::shouldReceive('disk')->with('local')->andReturn($disk);

    $this->post(route('backups.store'), ['backup' => uploadArchive($json), 'confirm' => true])->assertSessionHasErrors('backup');
    $this->assertDatabaseCount('vaults', 2);
    $this->assertDatabaseCount('problems', 2);
});

test('conversion fails without partial copies if block order is exhausted', function () {
    $problem = expandedProblem();
    $problem->blocks()->create(['type' => 'text', 'content' => 'Existing', 'position' => 1000000]);
    $this->post(route('vaults.sections.problems.blocks.convert', problemArgs($problem)))->assertSessionHasErrors('position');
    expect($problem->blocks()->count())->toBe(1);
    expect($problem->fresh()->corrected_solution)->toBe("    fixed();\n");
});

test('graph caps a larger workspace and reports the limit', function () {
    Problem::factory()->count(31)->create();
    $this->get(route('graph.index'))->assertInertia(fn (Assert $page) => $page->has('nodes', 30)->where('limited', true));
});

test('backup rejects inconsistent tag assignments even with a recomputed checksum', function () {
    Storage::fake('local');
    $problem = expandedProblem();
    app(ProblemTags::class)->sync($problem, ['PHP']);
    $archive = json_decode(app(KnowledgeBackup::class)->export(), true, 512, JSON_THROW_ON_ERROR);
    $archive['data']['problem_tag'] = [];
    $this->post(route('backups.store'), ['backup' => uploadArchive(signedArchive($archive)), 'confirm' => true])->assertSessionHasErrors('backup');
    $this->assertDatabaseCount('problems', 1);
    expect($problem->fresh()->tagRecords()->count())->toBe(1);
});
