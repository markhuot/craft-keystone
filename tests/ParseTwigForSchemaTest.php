<?php

use craft\helpers\App;
use markhuot\keystone\actions\CompileTwigComponent;
use markhuot\keystone\actions\GetComponentType;
use markhuot\keystone\actions\GetFileMTime;

it('throws on unknown component', function () {
    $this->expectException(RuntimeException::class);
    (new GetComponentType)->byType('foo/bar');
});

it('throws on bad template path', function () {
    $this->expectException(RuntimeException::class);
    (new CompileTwigComponent('site:not-a-real-template.twig', 'test/not-a-real-handle-either'))->handle();
});

it('caches component types by modification date', function () {
    $fqcn = (new CompileTwigComponent('site:component-with-fields.twig', 'test/component-with-fields'))->handle();
    $hash = sha1('test/component-with-fields');
    $filemtime = filemtime(Craft::$app->getView()->resolveTemplate('component-with-fields.twig', \craft\web\View::TEMPLATE_MODE_SITE));
    expect(App::parseEnv('@runtime/compiled_classes/ComponentType'.$hash.$filemtime.'.php'))->toBeFile();
});

it('compiles component name', function () {
    $fqcn = (new CompileTwigComponent('site:component-with-name.twig', 'test/component-with-name'))->handle(force: true);
    $component = new $fqcn;
    expect($component->getName())->toBe('foo');
});

it('does not re-cache when unchanged', function () {

})->todo();

it('re-caches on modification', function () {
    $oneHourAgo = (new \DateTime)->sub(new \DateInterval('P60M'))->getTimestamp();
    $now = (new \DateTime)->getTimestamp();

    $hash = sha1('test/component-with-fields');
    $filepath = Craft::$app->getView()->resolveTemplate('component-with-fields.twig', \craft\web\View::TEMPLATE_MODE_SITE);
    $touchCacheAt = function ($timestamp) use ($hash, $filepath) {
        GetFileMTime::mock($filepath, $timestamp);
        (new CompileTwigComponent('site:component-with-fields.twig', 'test/component-with-fields'))->handle();
        expect(App::parseEnv('@runtime/compiled_classes/ComponentType'.$hash.$timestamp.'.php'))->toBeFile();
    };

    $touchCacheAt($oneHourAgo);
    $touchCacheAt($now);

    expect(App::parseEnv('@runtime/compiled_classes/ComponentType'.$hash.$oneHourAgo.'.php'))->not->toBeFile();
});

it('gets field and slot schema', function () {
    $fqcn = (new CompileTwigComponent('site:component-with-fields.twig', 'test/component-with-fields'))->handle();

    expect(new $fqcn)
        ->getFieldDefinitions()->toHaveCount(1)
        ->getSlotDefinitions()->toHaveCount(1);
});

it('gets slot restrictions', function () {
    $fqcn = (new CompileTwigComponent('site:slot-with-restrictions.twig', 'test/slot-with-restrictions'))->handle();

    expect(new $fqcn)
        ->getSlotDefinitions()->toHaveCount(2)
        ->getSlotDefinition(null)->getWhitelist()->toContain('allowed/type')
        ->getSlotDefinition(null)->allows('allowed/type')->toBeTrue()
        ->getSlotDefinition(null)->allows('disallowed/type')->toBeFalse()
        ->getSlotDefinition('content')->getWhitelist()->toContain('allowed/type')
        ->getSlotDefinition('content')->allows('allowed/type')->toBeTrue()
        ->getSlotDefinition('content')->allows('disallowed/type')->toBeFalse();
});

it('gets exports', function () {
    $fqcn = (new CompileTwigComponent('site:export-icon.twig', 'test/export-icon'))->handle();
    (new $fqcn)->render(['exports' => $exports = new \markhuot\keystone\twig\Exports]);

    expect($exports)->icon->toBe('foo');
});
