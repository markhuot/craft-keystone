<?php

use markhuot\keystone\attributes\SpaceBetween;

it('skips when empty', function () {
    $classNames = (new SpaceBetween())->toAttributeArray();

    expect($classNames)->toBeEmpty();
});

it('renders margin-top', function () {
    $classNames = (new SpaceBetween(['y' => '1rem']))->toAttributeArray();
    $rules = \Craft::$app->getView()->getCssRules();

    expect($classNames['class'])->not->toBeEmpty();
    expect((string) $rules)->toBe('<style>.c0 > * + *{margin-top:1rem}</style>');
});

it('renders margin-left', function () {
    $classNames = (new SpaceBetween(['x' => '1rem']))->toAttributeArray();
    $rules = \Craft::$app->getView()->getCssRules();

    expect($classNames['class'])->not->toBeEmpty();
    expect((string) $rules)->toBe('<style>.c0 > * + *{margin-left:1rem}</style>');
});
