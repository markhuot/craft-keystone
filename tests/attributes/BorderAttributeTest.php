<?php

use markhuot\keystone\attributes\Border;

it('renders borders', function () {
    $border = new Border();

    expect($border->getCssRules())->not->toHaveKey('border');
});

it('renders border width shorthand', function () {
    $border = new Border(['width' => ['shorthand' => '10px']]);

    expect($border->getCssRules())->get('border-width')->toBe('10px');
});

it('renders expanded borders', function () {
    $border = new Border(['width' => ['useExpanded' => '1', 'expanded' => ['top' => '10px', 'left' => '']]]);

    expect($border->getCssRules())
        ->get('border-top-width')->toBe('10px')
        ->has('border-right-width')->toBeFalse()
        ->has('border-bottom-width')->toBeFalse()
        ->has('border-left-width')->toBeFalse();
});
