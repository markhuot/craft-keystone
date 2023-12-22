<?php

use markhuot\keystone\attributes\Background;
use markhuot\keystone\models\Component;

it('stores component data with attributes', function () {
    $component = Component::factory()->type('keystone/text')->create();
    $component->data->merge(['_attributes' => [Background::class => ['color' => '000000']]])->save();

    expect($component->render())->toContain('c0');

    $css = (string) Craft::$app->getView()->getCssRules();
    expect($css)
        ->not->toBeNull()
        ->toContain('.c0{background-color:#000000}');
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

it('renders attributes', function ($attrName, $props, $rules=null) {
    $attr = new $attrName($props);
    $output = $attr->getInputHtml();
    expect($output)->toContainEach(array_keys($props));
    $computedRules = $attr->getCssRules();
    expect($computedRules->toArray())->toEqualCanonicalizing($rules ?? $props);
})->with([
    [\markhuot\keystone\attributes\Alignment::class, ['justify-content' => 'center', 'align-items' => 'center']],
    [\markhuot\keystone\attributes\Background::class, ['color' => '000'], ['color' => '#000']],
    [\markhuot\keystone\attributes\Background::class, ['repeat' => false], ['background-repeat' => 'no-repeat']],
    [\markhuot\keystone\attributes\Background::class, ['size' => 'contain'], ['background-size' => 'contain']],
]);
