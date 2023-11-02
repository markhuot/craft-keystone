<?php

use markhuot\keystone\attributes\SpaceBetween;

use function markhuot\craftpest\helpers\test\dd;

it('skips when empty', function () {
    $classNames = (new SpaceBetween())->toAttributeArray();

    expect($classNames)->toBeEmpty();
});

it('renders margin-top', function () {
    $classNames = (new SpaceBetween(['y' => '1rem']))->toAttributeArray();
    $rules = \Craft::$app->getView()->getCssRules();
    dd($rules);

    expect($classNames['class'])->not->toBeEmpty();
    expect(current($rules))
        ->property->toBe('margin-top')
        ->value->toBe('1rem')
        ->selector->toBe('& > * + *');
});

it('renders margin-left', function () {
    $classNames = (new SpaceBetween(['x' => '1rem']))->toAttributeArray();
    $rules = \Craft::$app->getView()->getCssRules()->getRules();

    expect($classNames['class'])->not->toBeEmpty();
    expect(current($rules))
        ->rule->toBe('margin-left:1rem')
        ->selector->toBe('& > * + *');
});
