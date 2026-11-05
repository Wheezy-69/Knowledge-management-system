<?php

use Inertia\Testing\AssertableInertia as Assert;

test('the vault workspace is accessible without signing in', function () {
    $response = $this->get(route('vaults.index'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('vaults/Index'));
});
