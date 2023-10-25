<?php

use markhuot\keystone\models\Component;

it('stores component data with attributes', function () {
    $component = Component::factory()->type('keystone/text')->create();
    $component->data->merge(['_attributes' => [\markhuot\keystone\attributes\Background::class => ['color' => '000000']]])->save();

    expect($component->render())->toContain('c0');
    expect(current(Craft::$app->getView()->getCssRules() ?? []))
        ->not->toBeNull()
        ->property->toBe('background-color')
        ->value->toBe('#000000');
});
