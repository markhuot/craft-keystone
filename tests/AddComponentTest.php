<?php

use markhuot\craftpest\factories\Entry;
use markhuot\keystone\actions\AddComponent;
use function markhuot\keystone\helpers\base\app;

it('fills default slot content', function () {
    $entry = Entry::factory()->section('pages')->create();
    $field = app()->getFields()->getFieldByHandle('myKeystoneField');
    $component = (new AddComponent)->handle($entry->id, $field->id, 0, null, null, 'site/components/slot-with-defaults', []);

    expect($component->getSlot())
        ->toHaveCount(2)
        ->get(0)->data->get('text')->toBe('foo')
        ->get(1)->data->get('text')->toBe('bar');
});

it('fills nested default slot content', function () {
    $entry = Entry::factory()->section('pages')->create();
    $field = app()->getFields()->getFieldByHandle('myKeystoneField');
    $component = (new AddComponent)->handle($entry->id, $field->id, 0, null, null, 'site/components/slot-with-nested-defaults', []);

    expect($component->getSlot())
        ->toHaveCount(2)
        ->get(0)->data->get('text')->toBe('foo')
        ->get(1)->getSlot()->toHaveCount(1);
});
