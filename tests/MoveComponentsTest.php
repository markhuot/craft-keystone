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

it('moves child components above/below', function () {
    $components = collect([
        $sourceGrandParent = Component::factory()->create(),
            $sourceParent = Component::factory()->create(['path' => $sourceGrandParent->id]),
                $sourceChild = Component::factory()->create(['path' => implode('/', [$sourceGrandParent->id, $sourceParent->id])]),
        $targetParent = Component::factory()->create(['sortOrder' => 1]),
            $targetChild = Component::factory()->create(['path' => $targetParent->id]),
    ]);

    (new MoveComponent())->handle($sourceParent, $targetChild, 'below');
    $components->each->refresh();
    
    dd(Component::find()->collect()->map->toArray());
});

it('moves child components beforeend', function () {
    $components = collect([
        $parent = Component::factory()->create(['sortOrder' => 0]),
        $child = Component::factory()->create(['sortOrder' => 0, 'path' => $parent->id, 'level' => 1]),
        $target = Component::factory()->create(['sortOrder' => 1]),
    ]);

    (new MoveComponent())->handle($parent, $target, 'beforeend');
    $components->each->refresh();
    
    expect($parent)
        ->path->toBe((string)$target->id)
        ->level->toBe(1);

    expect($child)
        ->path->toBe(implode('/', [$target->id, $parent->id]))
        ->level->toBe(2);
});
