<?php

use markhuot\craftpest\factories\Entry;
use markhuot\keystone\actions\DuplicateComponentTree;
use markhuot\keystone\actions\EditComponentData;
use markhuot\keystone\models\Component;

it('edits component data in-place when only one reference is found', function () {
    $component = Component::factory()->type('keystone/text')->create();
    $originalDataId = $component->data->id;
    (new EditComponentData)->handle($component, ['foo' => 'bar']);

    expect($originalDataId)->toBe($component->data->id);
});

it('duplicates data when shared with multiple elements/revisions', function () {
    $component = Component::factory()->type('keystone/text')->create();
    $originalDataId = $component->data->id;
    $target = Entry::factory()->create();
    (new DuplicateComponentTree())->handle($component->element, $target, $component->field);
    (new EditComponentData)->handle($component, ['foo' => 'bar']);

    expect($originalDataId)->not->toBe($component->data->id);
});

it('bails on unchanged component data', function () {
    $component = Component::factory()->type('keystone/text')->create();
    (new EditComponentData)->handle($component, ['a' => 1, 'b' => 2]);
    $originalDataId = $component->data->id;
    $target = Entry::factory()->create();
    (new DuplicateComponentTree())->handle($component->element, $target, $component->field);
    (new EditComponentData)->handle($component, ['b' => 2, 'a' => 1]);

    expect($originalDataId)->toBe($component->data->id);
});
