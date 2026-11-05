<?php

use App\Models\Attempt;
use App\Models\Problem;
use App\Models\Section;
use App\Models\Vault;
use Inertia\Testing\AssertableInertia as Assert;

test('sections are created through their vault and can be updated', function () {
    $vault = Vault::factory()->create();
    $other = Vault::factory()->create();
    $this->post(route('vaults.sections.store', $vault), ['name' => 'Validation', 'vault_id' => $other->id])->assertRedirect();
    $section = Section::query()->sole();

    $this->assertDatabaseHas('sections', ['id' => $section->id, 'vault_id' => $vault->id]);
    $this->get(route('vaults.show', $vault))->assertInertia(fn (Assert $page) => $page->has('sections.data', 1));
    $this->get(route('vaults.show', $other))->assertInertia(fn (Assert $page) => $page->has('sections.data', 0));
    $this->put(route('vaults.sections.update', [$vault, $section]), ['name' => 'Queries', 'description' => 'Eloquent', 'vault_id' => $other->id])->assertRedirect();
    $this->assertDatabaseHas('sections', ['id' => $section->id, 'name' => 'Queries', 'vault_id' => $vault->id]);
});

test('invalid section data is rejected', function (array $data, string $field) {
    $vault = Vault::factory()->create();

    $this->post(route('vaults.sections.store', $vault), $data)->assertSessionHasErrors($field);
    $this->assertDatabaseCount('sections', 0);
})->with([
    'required' => [[], 'name'],
    'length' => [['name' => str_repeat('a', 121)], 'name'],
    'description' => [['name' => 'PHP', 'description' => str_repeat('a', 10001)], 'description'],
]);

test('sections cannot be accessed through another vault', function (string $method, string $action) {
    $section = Section::factory()->create();
    $other = Vault::factory()->create();

    $this->{$method}(route('vaults.sections.'.$action, [$other, $section]), ['name' => 'Changed'])->assertNotFound();
    $this->assertModelExists($section);
    expect($section->fresh()->name)->toBe($section->name);
})->with([['get', 'show'], ['put', 'update'], ['delete', 'destroy']]);

test('deleting a section cascades to its problems and attempts while keeping its vault', function () {
    $section = Section::factory()->create();
    $problem = Problem::factory()->for($section)->create();
    $attempt = Attempt::factory()->for($problem)->create();

    $this->delete(route('vaults.sections.destroy', [$section->vault, $section]))->assertRedirect(route('vaults.show', $section->vault));
    $this->assertModelMissing($section);
    $this->assertModelMissing($problem);
    $this->assertModelMissing($attempt);
    $this->assertModelExists($section->vault);
});
