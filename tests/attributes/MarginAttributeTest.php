<?php

use markhuot\keystone\attributes\Margin;

it('renders margins', function () {
    $margin = new Margin();

    expect($margin->getCssRules())->not->toHaveKey('margin');
});

it('renders margin shorthand', function () {
    $margin = new Margin(['shorthand' => '10px']);

    expect($margin->getCssRules())->get('margin')->toBe('10px');
});

it('renders expanded margins', function () {
    $margin = new Margin(['useExpanded' => '1', 'expanded' => ['top' => '10px', 'left' => '']]);

    expect($margin->getCssRules())
        ->get('margin-top')->toBe('10px')
        ->has('margin-right')->toBeFalse()
        ->has('margin-bottom')->toBeFalse()
        ->has('margin-left')->toBeFalse();
});
