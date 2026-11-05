<?php

use App\Models\Attempt;
use App\Models\Problem;
use App\Models\Section;
use Inertia\Testing\AssertableInertia as Assert;

test('problems retain their solution tags explanation and source whitespace', function () {
    $section = Section::factory()->create();
    $code = "    echo 'fixed';\n\n";
    $this->post(route('vaults.sections.problems.store', [$section->vault, $section]), [
        'title' => 'Why does validation fail?', 'programming_language' => 'php',
        'description' => 'A request fails', 'tags' => ['validation', 'php'],
        'corrected_solution' => $code, 'explanation' => 'Use the correct rule.',
    ])->assertRedirect();
    $problem = Problem::query()->sole();

    expect($problem->tags)->toBe(['validation', 'php']);
    expect($problem->corrected_solution)->toBe($code);
    $this->get(route('vaults.sections.problems.show', [$section->vault, $section, $problem]))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('problems/Show')->where('problem.explanation', 'Use the correct rule.'));
    $this->put(route('vaults.sections.problems.update', [$section->vault, $section, $problem]), [
        'title' => 'Fixed', 'programming_language' => 'javascript', 'tags' => [], 'corrected_solution' => null, 'explanation' => null,
    ])->assertRedirect();
    $this->assertDatabaseHas('problems', ['id' => $problem->id, 'title' => 'Fixed', 'corrected_solution' => null]);
});

test('problem validation rejects malformed fields', function (array $invalid, string $field) {
    $section = Section::factory()->create();

    $this->post(route('vaults.sections.problems.store', [$section->vault, $section]), array_replace([
        'title' => 'Example', 'programming_language' => 'php',
    ], $invalid))->assertSessionHasErrors($field);
    $this->assertDatabaseCount('problems', 0);
})->with([
    'missing title' => [['title' => ''], 'title'],
    'long title' => [['title' => str_repeat('a', 201)], 'title'],
    'missing language' => [['programming_language' => ''], 'programming_language'],
    'long language' => [['programming_language' => str_repeat('a', 41)], 'programming_language'],
    'long description' => [['description' => str_repeat('a', 50001)], 'description'],
    'non-array tags' => [['tags' => 'php'], 'tags'],
    'too many tags' => [['tags' => range(1, 13)], 'tags'],
    'long tag' => [['tags' => [str_repeat('a', 41)]], 'tags.0'],
    'duplicate tag' => [['tags' => ['PHP', 'php']], 'tags.0'],
    'long solution' => [['corrected_solution' => str_repeat('a', 100001)], 'corrected_solution'],
    'long explanation' => [['explanation' => str_repeat('a', 50001)], 'explanation'],
]);

test('search matches title and description without leaking out of a section', function () {
    $section = Section::factory()->create();
    Problem::factory()->for($section)->create(['title' => 'Validation issue', 'description' => null]);
    Problem::factory()->for($section)->create(['title' => 'Another', 'description' => 'VALIDATION notes']);
    Problem::factory()->for($section)->create(['title' => 'Unrelated', 'description' => null]);
    Problem::factory()->create(['title' => 'Validation elsewhere']);

    $this->get(route('vaults.sections.show', [$section->vault, $section, 'search' => 'validation']))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('problems.data', 2)->where('filters.search', 'validation'));
    $this->get(route('problems.index', ['search' => 'validation']))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('problems.data', 3)->has('problems.data.0.section.vault'));
    $this->get(route('problems.index', ['search' => "' OR 1=1 --"]))->assertInertia(fn (Assert $page) => $page->has('problems.data', 0));
});

test('invalid search is rejected', function () {
    $this->from(route('problems.index'))->get(route('problems.index', ['search' => ['not a string']]))->assertSessionHasErrors('search');
});

test('search pagination preserves the filter and result boundary', function () {
    Problem::factory()->count(16)->create(['title' => 'Matching lesson']);
    Problem::factory()->create(['title' => 'Something else']);

    $this->get(route('problems.index', ['search' => 'Matching', 'page' => 2]))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('problems.data', 1)
            ->where('problems.total', 16)->where('filters.search', 'Matching'));
});

test('problem parent cannot be changed through mass assignment', function () {
    $problem = Problem::factory()->create();
    $section = $problem->section;
    $other = Section::factory()->create();

    $this->put(route('vaults.sections.problems.update', [$section->vault, $section, $problem]), [
        'title' => 'Still here', 'programming_language' => 'php', 'section_id' => $other->id,
    ])->assertRedirect();
    expect($problem->fresh()->section_id)->toBe($section->id);
});

test('problems cannot be accessed through another section', function (string $method, string $action) {
    $problem = Problem::factory()->create();
    $other = Section::factory()->for($problem->section->vault)->create();

    $this->{$method}(route('vaults.sections.problems.'.$action, [$other->vault, $other, $problem]), [
        'title' => 'Changed', 'programming_language' => 'php',
    ])->assertNotFound();
    $this->assertModelExists($problem);
})->with([['get', 'show'], ['put', 'update'], ['delete', 'destroy']]);

test('creating problems under a section from another vault is rejected', function () {
    $section = Section::factory()->create();
    $other = Section::factory()->create();

    $this->post(route('vaults.sections.problems.store', [$other->vault, $section]), ['title' => 'Bad', 'programming_language' => 'php'])->assertNotFound();
    $this->assertDatabaseCount('problems', 0);
});

test('deleting a problem deletes its attempts but keeps its section', function () {
    $problem = Problem::factory()->create();
    $attempt = Attempt::factory()->for($problem)->create();
    $section = $problem->section;

    $this->delete(route('vaults.sections.problems.destroy', [$section->vault, $section, $problem]))->assertRedirect(route('vaults.sections.show', [$section->vault, $section]));
    $this->assertModelMissing($problem);
    $this->assertModelMissing($attempt);
    $this->assertModelExists($section);
});
