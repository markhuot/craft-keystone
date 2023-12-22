<?php

use craft\helpers\UrlHelper;
use markhuot\craftpest\factories\Entry;
use markhuot\craftpest\factories\User;
use markhuot\keystone\models\Component;

it('loads add panel', function () {
    $component = Component::factory()->type('keystone/text')->create();

    $this->actingAsAdmin()
        ->get(UrlHelper::actionUrl('keystone/components/add', [
            'elementId' => $component->elementId,
            'fieldId' => $component->fieldId,
            'path' => $component->path,
            'sortOrder' => 1,
        ]))
        ->assertOk();
});

it('loads edit panel', function ($type) {
    $component = Component::factory()->type($type)->create();

    $this->actingAsAdmin()
        ->get(UrlHelper::cpUrl('keystone/components/edit', $component->getQueryCondition()))
        ->assertOk();
})->with(collect(scandir(__DIR__.'/../src/templates/components'))
    ->filter(fn ($file) => ! str_starts_with($file, '.'))
    ->filter(fn ($file) => str_ends_with($file, '.twig'))
    ->map(fn ($file) => 'keystone/'.preg_replace('/\.twig$/', '', $file))
    ->all()
);

it('stores a component', function () {
    $component = Component::factory()
        ->type('keystone/section')
        ->elementId(Entry::factory()->section('pages')->create()->id)
        ->create();

    $this->actingAsAdmin()
        ->postJson(UrlHelper::actionUrl('keystone/components/store'), [
            'elementId' => $component->elementId,
            'fieldId' => $component->fieldId,
            'sortOrder' => 1,
            'path' => $component->getChildPath(),
            'type' => 'keystone/text',
        ])
        ->assertOk()
        ->assertJsonPath('message', 'Component added');
});

it('updates a component', function () {
    $component = Component::factory()
        ->type('keystone/text')
        ->elementId(Entry::factory()->section('pages')->create()->id)
        ->create();
    $component->data->merge(['text' => 'foo'])->save();

    $this->actingAsAdmin()
        ->postJson(UrlHelper::actionUrl('keystone/components/update'), [
            'id' => $component->id,
            'elementId' => $component->elementId,
            'fieldId' => $component->fieldId,
            'fields' => ['text' => 'bar'],
        ])
        ->assertOk()
        ->assertJsonPath('message', 'Component saved');

    $component->refresh();
    expect($component->data)->get('text')->toBe('bar');
});

it('deletes a component', function () {
    $component = Component::factory()
        ->type('keystone/text')
        ->elementId(Entry::factory()->section('pages')->create()->id)
        ->create();

    $this->actingAsAdmin()
        ->postJson(UrlHelper::actionUrl('keystone/components/delete'), [
            'id' => $component->id,
            'elementId' => $component->elementId,
            'fieldId' => $component->fieldId,
        ])
        ->assertOk()
        ->assertJsonPath('message', 'Component deleted');
});

it('moves a component', function () {
    $components = Component::factory()
        ->type('keystone/section')
        ->elementId(Entry::factory()->section('pages')->create()->id)
        ->count(2)
        ->create();

    $this->actingAsAdmin()
        ->postJson(UrlHelper::actionUrl('keystone/components/move'), [
            'source' => $components[0]->getQueryCondition(),
            'target' => $components[1]->getQueryCondition(),
            'position' => \markhuot\keystone\enums\MoveComponentPosition::AFTER,
        ])
        ->assertOk()
        ->assertJsonPath('message', 'Component moved');
});

it('toggles a component\'s disclosure', function () {
    /** @var Component $component */
    $component = Component::factory()
        ->type('keystone/section')
        ->elementId(Entry::factory()->section('pages')->create()->id)
        ->create();
    $url = UrlHelper::actionUrl('keystone/components/toggle-disclosure', [
        'id' => $component->id,
        'elementId' => $component->elementId,
        'fieldId' => $component->fieldId,
    ]);
    $user = User::factory()->admin(true)->create();

    $this->actingAs($user)->postJson($url)->assertOk();
    $component->refresh();
    expect($component->isCollapsed())->toBe(true);

    $this->actingAs($user)->postJson($url)->assertOk();
    $component->refresh();
    expect($component->isCollapsed())->toBe(false);
});
