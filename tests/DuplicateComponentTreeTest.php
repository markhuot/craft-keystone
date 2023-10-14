<?php

use markhuot\craftpest\factories\Entry;
use markhuot\keystone\models\Component;
use markhuot\keystone\models\ComponentData;

beforeEach(function () {
    $this->seed = function (array $sourceIds=[], array $destinationIds=[]) {
        $source = Entry::factory()->section('pages')->create();
        $destination = Entry::factory()->section('pages')->create();
        $field = Craft::$app->getFields()->getFieldByHandle('myKeystoneField');

        foreach ($sourceIds as $sortOrder => $sourceId) {
            $sourceId = is_array($sourceId) ? $sourceId : ['id' => $sourceId];
            Component::factory()->create(['elementId' => $source->id, 'fieldId' => $field->id, 'sortOrder' => $sortOrder, ...$sourceId]);
        }
        foreach ($destinationIds as $sortOrder => $destinationId) {
            $destinationId = is_array($destinationId) ? $destinationId : ['id' => $destinationId];
            Component::factory()->create(['elementId' => $destination->id, 'fieldId' => $field->id, 'sortOrder' => $sortOrder, ...$destinationId]);
        }

        return [$source, $destination, $field];
    };
});

it('inserts components during duplicate', function () {
    [$source, $destination, $field] = ($this->seed)([1,2], []);
    (new \markhuot\keystone\actions\DuplicateComponentTree)->handle($source, $destination, $field);

    $duplicates = Component::find()->where(['elementId' => $destination->id])->orderBy(['path' => 'asc'])->collect();
    expect($duplicates)->toHaveCount(2);
    expect($duplicates[0])->id->toBe(1);
    expect($duplicates[1])->id->toBe(2);
});

it('deletes components during duplicate', function () {
    [$source, $destination, $field] = ($this->seed)([1], [1,2]);
    (new \markhuot\keystone\actions\DuplicateComponentTree)->handle($source, $destination, $field);

    $duplicates = Component::find()->where(['elementId' => $destination->id])->orderBy(['path' => 'asc'])->collect();
    expect($duplicates)->toHaveCount(1);
    expect($duplicates)->first()->id->toBe(1);
});

it('updates components during duplicate', function() {
    [$source, $destination, $field] = ($this->seed)(
        [['id' => 1, 'sortOrder' => 1], ['id' => 2, 'sortOrder' => 0]],
        [['id' => 1, 'sortOrder' => 0], ['id' => 2, 'sortOrder' => 1]],
    );
    (new \markhuot\keystone\actions\DuplicateComponentTree)->handle($source, $destination, $field);

    $duplicates = Component::find()->where(['elementId' => $destination->id])->orderBy(['path' => 'asc'])->collect();
    expect($duplicates)->toHaveCount(2);
    expect($duplicates[0])->sortOrder->toBe(1);
    expect($duplicates[1])->sortOrder->toBe(0);
});

it('deletes and updates components during duplicate', function() {
    [$source, $destination, $field] = ($this->seed)(
        [['id' => 8, 'sortOrder' => 1], ['id' => 9, 'sortOrder' => 0]],
        [['id' => 7, 'sortOrder' => 0], ['id' => 8, 'sortOrder' => 1]],
    );
    (new \markhuot\keystone\actions\DuplicateComponentTree)->handle($source, $destination, $field);

    $duplicates = Component::find()->where(['elementId' => $destination->id])->orderBy(['path' => 'asc'])->collect();
    expect($duplicates)->toHaveCount(2);
    expect($duplicates[0])->id->toBe(8);
    expect($duplicates[0])->sortOrder->toBe(1);
    expect($duplicates[1])->sortOrder->toBe(0);
});
