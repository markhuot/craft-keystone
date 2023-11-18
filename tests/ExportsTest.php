<?php

use markhuot\keystone\actions\GetComponentType;
use markhuot\keystone\models\Component;
use markhuot\keystone\models\ComponentData;
use markhuot\keystone\twig\Exports;
use yii\base\ExitException;

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

it('skips exports when not accessed', function () {
    $component = Component::factory()->type('site/components/skipped-export')->create();
    $bar = $component->getType()->getExports()['exports']->get('bar');
    expect($bar)->toBe('baz');
});

it('gets exports', function () {
    $this->expectException(ExitException::class);
    $component = Component::factory()->type('site/components/skipped-export')->create();
    $component->getType()->getExports()['exports']->get('foo');
});

it('caches component type schema per type not per component instance', function () {
    $type = (new GetComponentType)->byType('site/components/schema-cache');
    $mock = Mockery::mock(get_class($type))
        ->makePartial()
        ->shouldReceive()->getExports()->with(true)->andReturn(['exports' => new Exports, 'props' => new ComponentData])->once()->getMock();

    $mock->getSchema();
    $mock->getSchema();
});

it('executes individual prop types as needed', function () {

})->todo();
