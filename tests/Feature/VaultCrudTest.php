<?php

use App\Models\Attempt;
use App\Models\Problem;
use App\Models\Section;
use App\Models\Vault;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('vaults can be created listed opened and updated', function () {
    $this->post(route('vaults.store'), ['name' => 'PHP', 'description' => 'Lessons'])
        ->assertRedirect();
    $vault = Vault::query()->sole();

    $this->get(route('vaults.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('vaults/Index')->has('vaults.data', 1)->where('vaults.data.0.name', 'PHP'));
    $this->get(route('vaults.show', $vault))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('vaults/Show')->where('vault.id', $vault->id)->has('sections.data', 0));
    $this->put(route('vaults.update', $vault), ['name' => 'Laravel', 'description' => null])
        ->assertRedirect()->assertSessionHas('success', 'Vault updated.');

    $this->assertDatabaseHas('vaults', ['id' => $vault->id, 'name' => 'Laravel', 'description' => null]);
});

test('vault validation prevents invalid records', function (array $payload, string $field) {
    $this->post(route('vaults.store'), $payload)->assertSessionHasErrors($field);

    $this->assertDatabaseCount('vaults', 0);
})->with([
    'missing name' => [[], 'name'],
    'long name' => [['name' => str_repeat('a', 121)], 'name'],
    'long description' => [['name' => 'PHP', 'description' => str_repeat('a', 10001)], 'description'],
]);

test('covers are uploaded served replaced and removed', function () {
    Storage::fake('local');
    $this->post(route('vaults.store'), ['name' => 'PHP', 'cover' => UploadedFile::fake()->image('cover.png')])->assertRedirect();
    $vault = Vault::query()->sole();
    $original = $vault->cover_path;
    Storage::disk('local')->assertExists($original);
    $this->get(route('vaults.cover', $vault))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');

    $this->post(route('vaults.update', $vault), ['_method' => 'put', 'name' => 'PHP', 'cover' => UploadedFile::fake()->image('new.jpg')])->assertRedirect();
    $replacement = $vault->fresh()->cover_path;
    Storage::disk('local')->assertMissing($original);
    Storage::disk('local')->assertExists($replacement);

    $this->put(route('vaults.update', $vault), ['name' => 'PHP', 'remove_cover' => true])->assertRedirect();
    expect($vault->fresh()->cover_path)->toBeNull();
    Storage::disk('local')->assertMissing($replacement);
    $this->get(route('vaults.cover', $vault))->assertNotFound();
});

test('cover validation rejects executable and oversized files', function () {
    Storage::fake('local');
    foreach ([UploadedFile::fake()->create('bad.svg', 1, 'image/svg+xml'), UploadedFile::fake()->image('big.png')->size(2049)] as $file) {
        $this->post(route('vaults.store'), ['name' => 'PHP', 'cover' => $file])->assertSessionHasErrors('cover');
    }

    $this->assertDatabaseCount('vaults', 0);
    expect(Storage::disk('local')->allFiles())->toBeEmpty();
});

test('deleting a vault cascades through its children and removes its cover', function () {
    Storage::fake('local');
    Storage::disk('local')->put('vault-covers/test.png', 'image');
    $vault = Vault::factory()->create(['cover_path' => 'vault-covers/test.png']);
    $section = Section::factory()->for($vault)->create();
    $problem = Problem::factory()->for($section)->create();
    $attempt = Attempt::factory()->for($problem)->create();
    $unrelated = Vault::factory()->create();

    $this->delete(route('vaults.destroy', $vault))->assertRedirect(route('vaults.index'));

    $this->assertModelMissing($vault);
    $this->assertModelMissing($section);
    $this->assertModelMissing($problem);
    $this->assertModelMissing($attempt);
    $this->assertModelExists($unrelated);
    Storage::disk('local')->assertMissing('vault-covers/test.png');
});

test('vaults are paginated', function () {
    Vault::factory()->count(13)->create();

    $this->get(route('vaults.index'))->assertInertia(fn (Assert $page) => $page
        ->has('vaults.data', 12)->where('vaults.total', 13));
    $this->get(route('vaults.index', ['page' => 2]))->assertInertia(fn (Assert $page) => $page->has('vaults.data', 1));
});

test('a failed cover write returns validation feedback without creating a vault', function () {
    $disk = Mockery::mock(FilesystemAdapter::class);
    $disk->shouldReceive('putFileAs')->once()->andReturn(false);
    Storage::shouldReceive('disk')->with('local')->andReturn($disk);

    $this->post(route('vaults.store'), ['name' => 'PHP', 'cover' => UploadedFile::fake()->image('cover.png')])
        ->assertSessionHasErrors(['cover' => 'The cover could not be saved. Please try again.']);
    $this->assertDatabaseCount('vaults', 0);
});
