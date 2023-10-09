<?php

use markhuot\keystone\models\Component;

it('duplicates components', function () {
    $source = new \craft\elements\Entry;
    $source->id = 1;
    $destination = new \craft\elements\Entry;
    $destination->id = 2;
    $field = new \markhuot\keystone\fields\Keystone;
    $field->id = 1;
    $data = new \markhuot\keystone\models\ComponentData;
    $data->type = 'keystone/test';
    $data->save();
    $component = Component::factory()->create(['dataId' => $data->id]);
    $child = Component::factory()->create(['dataId' => $data->id, 'path' => $component->id, 'level' => 1]);
    (new \markhuot\keystone\actions\DuplicateComponentTree)->handle($source, $destination, $field);

    $duplicates = Component::find()->where(['elementId' => 2])->orderBy(['path' => 'asc'])->collect();
    expect($duplicates[0])->path->toBeNull();
    expect($duplicates[1])->path->toBe((string)$duplicates[0]->id);
});
