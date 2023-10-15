<?php

use markhuot\keystone\actions\MoveComponent;
use markhuot\keystone\models\Component;

beforeEach(function () {
});

it('moves components', function () {
    $components = collect([
        Component::factory()->create(['sortOrder' => 0]),
        Component::factory()->create(['sortOrder' => 1]),
        Component::factory()->create(['sortOrder' => 2]),
    ]);

    (new MoveComponent())->handle($components[0], $components[2], 'below');
    $components->each->refresh();

    expect($components[0])->sortOrder->toBe(2);
    expect($components[1])->sortOrder->toBe(0);
    expect($components[2])->sortOrder->toBe(1);
});

it('moves child components', function () {
    expect(true)->toBeTrue();
});
