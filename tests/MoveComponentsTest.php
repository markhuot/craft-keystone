<?php

use markhuot\keystone\actions\MakeModelFromArray;
use markhuot\keystone\actions\MoveComponent;
use markhuot\keystone\enums\MoveComponentPosition;
use markhuot\keystone\models\Component;
use markhuot\keystone\models\http\MoveComponentRequest;

beforeEach(function () {
    $this->components = collect([
        'sourceGrandParent' => $sourceGrandParent = Component::factory()->create(),
        'sourceParent' => $sourceParent = Component::factory()->create(['path' => $sourceGrandParent->id]),
        'sourceChild' => Component::factory()->create(['path' => implode('/', [$sourceGrandParent->id, $sourceParent->id])]),
        'targetParent' => $targetParent = Component::factory()->create(['sortOrder' => 1]),
        'targetChild' => Component::factory()->create(['path' => $targetParent->id]),
    ]);
});

it('parses post data', function () {
    [$source, $target] = Component::factory()->count(2)->create();
    $data = (new MakeModelFromArray())->handle(MoveComponentRequest::class, [
        'source' => ['id' => $source->id, 'fieldId' => $source->fieldId, 'elementId' => $source->elementId],
        'target' => ['id' => $target->id, 'fieldId' => $target->fieldId, 'elementId' => $target->elementId],
        'position' => 'beforeend',
    ]);

    expect($data)
        ->errors->toBeEmpty()
        ->source->getQueryCondition()->toEqualCanonicalizing($source->getQueryCondition())
        ->target->getQueryCondition()->toEqualCanonicalizing($target->getQueryCondition());
});

it('errors on bad post data', function () {
    [$source, $target] = Component::factory()->count(2)->create();
    $data = (new MakeModelFromArray())->handle(MoveComponentRequest::class, [
        'source' => ['id' => $source->id, 'fieldId' => $source->fieldId, 'elementId' => $source->elementId],
        'target' => ['id' => 'foo', 'fieldId' => $target->fieldId, 'elementId' => $target->elementId],
    ]);

    expect($data->errors)
        ->target->not->toBeNull()
        ->position->not->toBeNull();
});

it('moves components', function () {
    $components = collect([
        Component::factory()->create(['sortOrder' => 0]),
        Component::factory()->create(['sortOrder' => 1]),
        Component::factory()->create(['sortOrder' => 2]),
    ]);

    (new MoveComponent())->handle($components[0], $components[2], MoveComponentPosition::AFTER);
    $components->each->refresh();

    expect($components[0])->sortOrder->toBe(2);
    expect($components[1])->sortOrder->toBe(0);
    expect($components[2])->sortOrder->toBe(1);
});

it('moves child components above/below', function () {
    ['sourceParent' => $sourceParent,
        'sourceChild' => $sourceChild,
        'targetParent' => $targetParent,
        'targetChild' => $targetChild,
    ] = $this->components;

    (new MoveComponent())->handle($sourceParent, $targetChild, MoveComponentPosition::AFTER);
    $this->components->each->refresh();

    expect($sourceParent)
        ->path->toBe(implode('/', [$targetParent->id]))
        ->level->toBe(1);

    expect($sourceChild)
        ->path->toBe(implode('/', [$targetParent->id, $sourceParent->id]))
        ->level->toBe(2);
});

it('moves root children components above/below', function () {
    ['sourceGrandParent' => $sourceGrandParent,
        'sourceParent' => $sourceParent,
        'sourceChild' => $sourceChild,
        'targetParent' => $targetParent,
        'targetChild' => $targetChild,
    ] = $this->components;

    (new MoveComponent())->handle($sourceGrandParent, $targetChild, MoveComponentPosition::AFTER);
    $this->components->each->refresh();

    expect($sourceGrandParent)
        ->path->toBe(implode('/', [$targetParent->id]))
        ->level->toBe(1);

    expect($sourceParent)
        ->path->toBe(implode('/', [$targetParent->id, $sourceGrandParent->id]))
        ->level->toBe(2);

    expect($sourceChild)
        ->path->toBe(implode('/', [$targetParent->id, $sourceGrandParent->id, $sourceParent->id]))
        ->level->toBe(3);
});

it('moves child components beforeend', function () {
    $components = collect([
        $parent = Component::factory()->create(),
        $child = Component::factory()->create(['path' => $parent->id]),
        $target = Component::factory()->create(['sortOrder' => 1]),
    ]);

    (new MoveComponent())->handle($parent, $target, MoveComponentPosition::BEFOREEND);
    $components->each->refresh();

    expect($parent)
        ->path->toBe((string) $target->id)
        ->level->toBe(1);

    expect($child)
        ->path->toBe(implode('/', [$target->id, $parent->id]))
        ->level->toBe(2);
});

it('moves child trees beforeend', function () {
    ['sourceGrandParent' => $sourceGrandParent,
        'sourceParent' => $sourceParent,
        'sourceChild' => $sourceChild,
        'targetParent' => $targetParent,
        'targetChild' => $targetChild,
    ] = $this->components;

    (new MoveComponent())->handle($sourceGrandParent, $targetParent, MoveComponentPosition::BEFOREEND);
    $this->components->each->refresh();

    expect($targetParent)->path->toBe(null)->sortOrder->toBe(0);
    expect($targetChild)->path->toBe(implode('/', [$targetParent->id]))->sortOrder->toBe(0);
    expect($sourceGrandParent)->path->toBe(implode('/', [$targetParent->id]))->sortOrder->toBe(1);
    expect($sourceParent)->path->toBe(implode('/', [$targetParent->id, $sourceGrandParent->id]))->sortOrder->toBe(0);
    expect($sourceChild)->path->toBe(implode('/', [$targetParent->id, $sourceGrandParent->id, $sourceParent->id]))->sortOrder->toBe(0);
});
