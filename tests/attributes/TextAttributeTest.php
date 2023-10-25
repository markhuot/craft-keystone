<?php

use markhuot\keystone\attributes\Text;

it('skips color when not set', function () {
    $text = (new Text());

    expect($text->getCssRules())->not->toHaveKey('color');
});

it('renders color with default alpha', function () {
    $text = (new Text(['color' => 'ffffff']));

    expect($text->getCssRules())->get('color')->toBe('rgb(255 255 255/1)');
});

it('renders alpha with default color', function () {
    $text = (new Text(['alpha' => '0.5']));

    expect($text->getCssRules())->get('color')->toBe('rgb(0 0 0/0.5)');
});

it('renders color and alpha', function () {
    $text = (new Text(['color' => 'ff00ff', 'alpha' => '0.5']));

    expect($text->getCssRules())->get('color')->toBe('rgb(255 0 255/0.5)');
});
