<?php

use markhuot\craftpest\factories\User;
use markhuot\keystone\models\Component;

// In order to interact with disclosures you must be logged in
// Anonymous users can't set disclosure states
beforeEach()->actingAsAdmin();

it('gets default open disclosure state', function () {
    $component = Component::factory()->type('keystone/section')->create();

    expect($component->isCollapsed())->toBeFalse();
});

it('overrides default open disclosure state', function () {
    $component = Component::factory()->type('keystone/section')->create();
    $component->disclosure->state = 'closed';

    expect($component->isCollapsed())->toBeTrue();
});

it('gets default closed disclosure state', function () {
    $component = Component::factory()->type('keystone/entry')->create();

    expect($component->isCollapsed())->toBeTrue();
});

it('overrides default closed disclosure state', function () {
    $component = Component::factory()->type('keystone/entry')->create();
    $component->disclosure->state = 'open';

    expect($component->isCollapsed())->toBeFalse();
});

it('respects user preferences by storing unique disclosures per user', function () {
    [$user1, $user2] = User::factory()->count(2)->create();
    $component = Component::factory()->type('keystone/entry')->create();

    $this->actingAs($user1);
    $component1 = Component::findOne($component->getQueryCondition());
    $component1->disclosure->state = 'open';
    $component1->disclosure->save();

    $this->actingAs($user2);
    $component2 = Component::findOne($component->getQueryCondition());
    $component2->disclosure->state = 'closed';
    $component2->disclosure->save();

    expect($component1->getQueryCondition())->toEqualCanonicalizing($component2->getQueryCondition());
    expect($component1->disclosure->id)->not->toEqual($component2->disclosure->id);
    expect($component1->isCollapsed())->not->toBe($component2->isCollapsed());
});
