<?php

use markhuot\craftpest\factories\Asset;
use markhuot\keystone\models\Component;

use function markhuot\craftpest\helpers\test\dd;

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
        ->assertQueryCount(3);
});