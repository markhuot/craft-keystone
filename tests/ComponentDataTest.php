<?php

use markhuot\craftpest\factories\Entry;
use markhuot\keystone\actions\DuplicateComponentTree;
use markhuot\keystone\actions\EditComponentData;
use markhuot\keystone\models\Component;
use markhuot\keystone\models\ComponentData;

it('loads component data', function () {
    $entry = Entry::factory()->section('pages')->create();
    $field = Component::factory()
        ->elementId($entry->id)
        ->type('keystone/text')
        ->count(3)
        ->create()
        ->first()->getField();
    $entry = $entry->refresh();

    $this->beginBenchmark();
    $fragment = $entry->{$field->handle};
    // load each of the data relations to make sure we don't incur an N+1 query
    $fragment->getSlot()->map(fn ($c) => $c->data);
    $this->endBenchmark()->assertQueryCount(2/* one for the components and one for the data */);

    expect($fragment->getSlot())
        ->toHaveCount(3)
        ->first()->data->type->toBe('keystone/text');
});

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

it('unsets component data', function () {
    $data = new ComponentData;
    $data['foo'] = 'bar';

    expect($data->data['foo'])->toBe('bar');

    unset($data['foo']);

    expect($data->data)->toBeArray()->toBeEmpty();
});
