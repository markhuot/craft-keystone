<?php

use markhuot\craftpest\factories\Entry;
use markhuot\craftpest\web\TestableResponse;
use markhuot\keystone\models\Component;

it('renders template component', function () {
    $component = Component::factory()->type('keystone/template')->create();
    $component->data->merge(['template' => 'basic-template'])->save();

    expect(trim($component->render()))->toBe('foo');
});

it('renders entry links', function () {
    $entry = Entry::factory()->create();

    $entryComponent = Component::factory()->type('keystone/entry')->create();
    $entryComponent->data->merge(['entry' => [$entry->id]])->save();

    $templateComponent = Component::factory()->type('keystone/template')->path($entryComponent->id)->create();
    $templateComponent->data->merge(['template' => 'cp:keystone/entry/link'])->save();

    $html = $entryComponent->render();
    expect((new TestableResponse(['content' => $html]))->querySelector('a'))
        ->getNodeOrNodes(fn ($node) => $node->getNode(0)->getAttribute('href'))->toBe($entry->uri)
        ->getText()->toBe($entry->title);
});
