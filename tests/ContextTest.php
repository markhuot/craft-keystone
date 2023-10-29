<?php

use markhuot\keystone\models\Component;

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

it('carries context down the tree', function () {
    $section = Component::factory()->type('keystone/section')->create();
    $link = Component::factory()->type('keystone/link')->path($section->id)->create();
    $link->data->merge(['href' => '{href}'])->save();
    $text = Component::factory()->type('keystone/text')->path(implode('/', [$section->id, $link->id]))->create();
    $text->data->merge(['text' => '{label}'])->save();

    $response = $section->setContext(['href' => '/made/up/href', 'label' => 'My Great Label'])->render();
    expect($response)
        ->toContain('href="/made/up/href"')
        ->toContain('My Great Label');
});
