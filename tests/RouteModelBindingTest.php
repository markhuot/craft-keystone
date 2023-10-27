<?php

use craft\base\ElementInterface;
use markhuot\craftpest\factories\Entry;
use markhuot\keystone\actions\MakeModelFromArray;
use markhuot\keystone\enums\MoveComponentPosition;
use markhuot\keystone\models\Component;
use markhuot\keystone\models\http\AddComponentRequest;
use markhuot\keystone\models\http\MoveComponentRequest;

it('loads top level models', function () {
    $createdComponent = Component::factory()->create();
    $foundComponent = (new MakeModelFromArray)->handle(Component::class, $createdComponent->getQueryCondition());

    expect($createdComponent->id)->toBe($foundComponent->id);
});

it('evaluates rules for bad data', function () {
    $formData = (new MakeModelFromArray)->handle(MoveComponentRequest::class, ['position' => MoveComponentPosition::BEFOREEND->value]);

    expect($formData)
        ->errors->toHaveKeys(['source', 'target'])
        ->errors->not->toHaveKey('position');
});

it('gets elementinterface properties', function () {
    $entry = Entry::factory()->create();
    $formData = (new MakeModelFromArray)->handle(AddComponentRequest::class, ['element' => $entry->id]);

    expect($formData)->element->id->toBe($entry->id);
});

it('gets elementinterface models', function () {
    $entry = Entry::factory()->create();
    $formData = (new MakeModelFromArray)->handle(ElementInterface::class, $entry->id);

    expect($formData)->id->toBe($entry->id);
});
