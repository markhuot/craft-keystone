<?php

use markhuot\keystone\attributes\Background;
use markhuot\keystone\models\Component;

it('stores component data with attributes', function () {
    $component = Component::factory()->type('keystone/text')->create();
    $component->data->merge(['_attributes' => [Background::class => ['color' => '000000']]])->save();

    expect($component->render())->toContain('c0');
    expect(current(Craft::$app->getView()->getCssRules() ?? []))
        ->not->toBeNull()
        ->property->toBe('background-color')
        ->value->toBe('#000000');
});

it('preserves empty attributes so they can be added to the UI without values', function () {
    $component = Component::factory()->type('keystone/text')->create();
    $component->data->merge(['_attributes' => [Background::class => null]])->save();

    expect($component->data->getDataAttributes())->toHaveKeys([Background::class]);
});

it('clears background color when empty', function () {
    $component = Component::factory()->type('keystone/text')->create();
    $component->data->merge(['_attributes' => [Background::class => ['color' => '']]])->save();

    expect($component->getAttributeBag()->toArray())->class->toBe('');
});
