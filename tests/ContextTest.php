<?php

use markhuot\keystone\models\Component;

use function markhuot\craftpest\helpers\test\dd;

it('saves type', function () {
    $component = Component::factory()->type('site/components/with-context')->create();
    $component->refresh();
    
    expect($component->data->type)->toBe('site/components/with-context');
});

it('renders context', function () {
    $component = Component::factory()->type('site/components/with-context')->create();
    $response = $component->setContext(['foo' => 'bar'])->render();

    expect($response)->toBe('bar');
});

it('renders collections with context', function () {
    $fragment = Component::factory()->type('keystone/fragment')->create();
    Component::factory()->type('site/components/with-context')->path($fragment->id)->create();

    $response = $fragment->getSlot()->render(['foo' => 'bar']);
    expect(trim((string) $response))->toBe('bar');
});

it('renders object templates from context', function () {
    $text = Component::factory()->type('keystone/text')->create();
    $text->data->merge(['text' => '{foo}'])->save();

    expect(trim(strip_tags($text->setContext(['foo' => 'bar'])->render())))->toBe('bar');
});

it('renders object templates from slot context', function () {
    $fragment = Component::factory()->type('keystone/fragment')->create();
    $text = Component::factory()->type('keystone/text')->path($fragment->id)->create();
    $text->data->merge(['text' => '{foo}'])->save();

    $response = $fragment->getSlot()->render(['foo' => 'bar']);
    expect(trim(strip_tags((string) $response)))->toBe('bar');
});