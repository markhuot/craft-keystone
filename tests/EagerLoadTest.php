<?php

use markhuot\craftpest\factories\Asset;
use markhuot\keystone\models\Component;

it('passes loaded components down so they\'re not refetched', function () {
    $grandparent = Component::factory()->type('keystone/section')->create();
    $parent = Component::factory()->type('keystone/section')->path($grandparent->id)->create();
    $child = Component::factory()->type('keystone/section')->path([$grandparent->id, $parent->id])->create();
    $grandchild = Component::factory()->type('keystone/section')->path([$grandparent->id, $parent->id, $child->id])->create();

    // Load our data in outside of our benchmark so we can ensure there are no
    // database queries below this point
    $grandparent->getSlot();

    $this->beginBenchmark();
    expect($grandparent->getSlot())

        // Check that only the parent is slotted in to the grandparent
        ->toHaveCount(1)

        // Check that only the child is slotted in to the parent
        ->first()->getSlot()->toHaveCount(1)

        // Check that only the grandchild is slotted in to the child
        ->first()->getSlot()->first()->getSlot()->toHaveCount(1);

    $this->endBenchmark()->assertQueryCount(0);
});

it('eager loads assets', function () {
    $fragment = Component::factory()
        ->type('keystone/fragment')
        ->create();
    $assets = Component::factory()
        ->type('keystone/asset')
        ->path($fragment->id)
        ->count(3)
        ->create();
    $assets->each(fn ($c) => $c->data
        ->merge(['asset' => [Asset::factory()->volume('local')->create()->id]])
        ->save()
    );

    $this->beginBenchmark();
    $fragment->getSlot()->each(fn ($c) => $c->getProp('asset')->one());
    $this->endBenchmark()
        ->assertQueryCount(4);
});
