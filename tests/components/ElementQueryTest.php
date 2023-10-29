<?php

use markhuot\craftpest\factories\Entry;
use markhuot\keystone\models\Component;

it('renders element queries', function () {
    $entry = Entry::factory()->title('foobarbaz')->create();
    $component = Component::factory()->type('keystone/elementquery')->create();
    $test = Component::factory()->type('keystone/text')->path($component->id)->create();
    $test->data->merge(['text' => '{element.title}'])->save();
    $response = $component->render();
    expect($response)->toContain('foobarbaz');
});
