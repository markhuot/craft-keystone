<?php

use markhuot\craftpest\factories\Entry;
use markhuot\keystone\models\Component;

it('renders element queries', function () {
    $entry = Entry::factory()->title('foobarbaz')->create();
    $component = Component::factory()->type('keystone/entryquery')->create();
    $test = Component::factory()->type('keystone/text')->path($component->id)->create();
    $test->data->merge(['text' => '{entry.title}'])->save();
    $response = $component->render();
    expect($response)->toContain('foobarbaz');
});
