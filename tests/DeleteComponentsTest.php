<?php

use markhuot\keystone\actions\DeleteComponent;
use markhuot\keystone\factories\Component;

it('deletes components', function () {
    $components = collect([
        Component::factory()->create(),
        Component::factory()->create(['sortOrder' => 1]),
        Component::factory()->create(['sortOrder' => 2]),
    ]);

    (new DeleteComponent)->handle($components[1]);
    $components->map->refresh();

    expect($components[0])->sortOrder->toBe(0);
    expect($components[2])->sortOrder->toBe(1);
});

it('deletes components with trees', function () {
    $components = collect([
        $sibling1 = Component::factory()->create(),
        $parent = Component::factory()->create(['sortOrder' => 1]),
        $child = Component::factory()->create(['path' => $parent->id]),
        $grandChild = Component::factory()->create(['path' => $parent->id.'/'.$child->id]),
        $sibling2 = Component::factory()->create(['sortOrder' => 2]),
    ]);

    (new DeleteComponent)->handle($parent);
    $components->map->refresh();

    expect($sibling1)->sortOrder->toBe(0);
    expect($sibling2)->sortOrder->toBe(1);
    expect($parent)->refresh()->toBeFalse();
    expect($child)->refresh()->toBeFalse();
    expect($grandChild)->refresh()->toBeFalse();
});
