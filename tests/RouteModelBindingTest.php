<?php

use craft\base\ElementInterface;
use markhuot\craftpest\factories\Entry;
use markhuot\keystone\actions\MakeModelFromArray;
use markhuot\keystone\enums\MoveComponentPosition;
use markhuot\keystone\models\Component;
use markhuot\keystone\models\http\MoveComponentRequest;
use markhuot\keystone\models\http\StoreComponentRequest;
use markhuot\keystone\tests\models\ElementToElementIdTest;
use markhuot\keystone\tests\models\OptionalFieldsTest;
use markhuot\keystone\tests\models\RequiredFieldsTest;

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
    $formData = (new MakeModelFromArray)->handle(StoreComponentRequest::class, ['element' => $entry->id]);

    expect($formData)->element->id->toBe($entry->id);
});

it('gets elementinterface models', function () {
    $entry = Entry::factory()->create();
    $formData = (new MakeModelFromArray)->handle(ElementInterface::class, $entry->id);

    expect($formData)->id->toBe($entry->id);
});

it('automatically translates element to elementId', function () {
    $entry = Entry::factory()->create();
    $formData = (new MakeModelFromArray)->handle(ElementToElementIdTest::class, ['elementId' => $entry->id]);

    expect($formData)->element->id->toBe($entry->id);
});

it('supports #[Required] attribute', function () {
    $rules = (new RequiredFieldsTest)->rules();

    expect($rules)->toHaveCount(1);
    expect($rules[0][0])->toBe(['foo']);
    expect($rules[0][1])->toBe('required');
});

it('supports #[Safe] attribute', function () {
    $formData = (new MakeModelFromArray)->handle(OptionalFieldsTest::class, ['foo' => 'foo', 'bar' => 'bar']);

    expect($formData)->foo->toBe('foo')->bar->toBe('bar');
});
