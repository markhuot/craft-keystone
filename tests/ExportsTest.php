<?php

use markhuot\keystone\models\Component;
use Twig\Error\RuntimeError;

it('nulls circular references', function () {
    $component = Component::factory()->type('site/components/dynamic-prop-types')->create();
    $component->data->merge(['foo' => 'bar']);
    $exports = $component->getType()->getExports();

    expect($exports)->propTypes->foo->placeholder
        ->toBe(null);
});

it('gets data even with circular references', function () {
    $component = Component::factory()->type('site/components/dynamic-prop-types')->create();
    $component->data->merge(['foo' => 'bar']);

    expect(trim($component->render()))->toBe('bar');
});

it('gets dynamic summaries', function () {
    $component = Component::factory()->type('site/components/summary-export')->create();
    $component->data->merge(['foo' => 'bar']);

    expect($component->getSummary())->toBe('bar');
});

it('skips exports unless instructed', function () {
    $component = Component::factory()->type('site/components/skipped-export')->create();
    $component->render();
});

it('gets exports when instructed', function() {
    $this->expectException(RuntimeError::class);
    $component = Component::factory()->type('site/components/skipped-export')->create();
    $component->getType()->getExports();
});
