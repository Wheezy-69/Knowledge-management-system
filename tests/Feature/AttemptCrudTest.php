<?php

use App\Models\Attempt;
use App\Models\Problem;
use Inertia\Testing\AssertableInertia as Assert;

test('attempts can be created edited reordered and deleted without changing the corrected solution', function () {
    $problem = Problem::factory()->create(['corrected_solution' => 'fixed']);
    $args = [$problem->section->vault, $problem->section, $problem];
    $code = "    original();\n";
    $this->from(route('vaults.sections.problems.show', $args))->post(route('vaults.sections.problems.attempts.store', $args), ['code' => $code, 'notes' => 'First idea', 'position' => 2])->assertRedirect();
    $attempt = Attempt::query()->sole();
    $this->post(route('vaults.sections.problems.attempts.store', $args), ['code' => 'earlier();', 'position' => 1])->assertRedirect();
    expect($attempt->code)->toBe($code);

    $this->get(route('vaults.sections.problems.show', $args))->assertInertia(fn (Assert $page) => $page
        ->has('problem.attempts', 2)->where('problem.attempts.0.position', 1)->where('problem.attempts.1.position', 2));
    $this->put(route('vaults.sections.problems.attempts.update', [...$args, $attempt]), ['code' => 'updated();', 'notes' => 'New lesson', 'position' => 3])->assertRedirect();
    $this->assertDatabaseHas('attempts', ['id' => $attempt->id, 'position' => 3, 'code' => 'updated();']);
    $this->delete(route('vaults.sections.problems.attempts.destroy', [...$args, $attempt]))->assertRedirect();
    $this->assertModelMissing($attempt);
    expect($problem->fresh()->corrected_solution)->toBe('fixed');
});

test('attempt validation rejects invalid fields', function (array $invalid, string $field) {
    $problem = Problem::factory()->create();

    $this->post(route('vaults.sections.problems.attempts.store', [$problem->section->vault, $problem->section, $problem]), array_replace(['code' => 'test();', 'position' => 1], $invalid))->assertSessionHasErrors($field);
    $this->assertDatabaseCount('attempts', 0);
})->with([
    'required code' => [['code' => ''], 'code'],
    'whitespace code' => [['code' => '   '], 'code'],
    'long code' => [['code' => str_repeat('a', 100001)], 'code'],
    'required order' => [['position' => null], 'position'],
    'positive order' => [['position' => 0], 'position'],
    'integer order' => [['position' => 1.5], 'position'],
    'bounded order' => [['position' => 1000001], 'position'],
    'long notes' => [['notes' => str_repeat('a', 10001)], 'notes'],
]);

test('attempt order is unique within a problem and editing keeps its own position', function () {
    $attempt = Attempt::factory()->create();
    $problem = $attempt->problem;
    $args = [$problem->section->vault, $problem->section, $problem];

    $this->post(route('vaults.sections.problems.attempts.store', $args), ['code' => 'duplicate', 'position' => 1])->assertSessionHasErrors('position');
    $this->put(route('vaults.sections.problems.attempts.update', [...$args, $attempt]), ['code' => 'edited', 'position' => 1])->assertRedirect()->assertSessionHasNoErrors();
    $other = Problem::factory()->create();
    $this->post(route('vaults.sections.problems.attempts.store', [$other->section->vault, $other->section, $other]), ['code' => 'allowed', 'position' => 1])->assertRedirect()->assertSessionHasNoErrors();
    $this->assertDatabaseCount('attempts', 2);
});

test('attempts cannot be accessed through another problem', function (string $method, string $action) {
    $attempt = Attempt::factory()->create();
    $other = Problem::factory()->for($attempt->problem->section)->create();

    $this->{$method}(route('vaults.sections.problems.attempts.'.$action, [$other->section->vault, $other->section, $other, $attempt]), ['code' => 'changed', 'position' => 1])->assertNotFound();
    expect($attempt->fresh()->code)->toBe($attempt->code);
})->with([['put', 'update'], ['delete', 'destroy']]);

test('attempt parent cannot be forged when creating', function () {
    $problem = Problem::factory()->create();
    $other = Problem::factory()->create();

    $this->post(route('vaults.sections.problems.attempts.store', [$problem->section->vault, $problem->section, $problem]), [
        'code' => 'safe', 'position' => 1, 'problem_id' => $other->id,
    ])->assertRedirect();
    $this->assertDatabaseHas('attempts', ['problem_id' => $problem->id, 'code' => 'safe']);
});
