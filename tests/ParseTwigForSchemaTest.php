<?php

use craft\helpers\App;
use markhuot\keystone\actions\CompileTwigComponent;
use markhuot\keystone\actions\GetComponentType;
use markhuot\keystone\actions\GetFileMTime;

it('throws on unknown component', function () {
    $this->expectException(RuntimeException::class);
    (new GetComponentType)->byType('foo/bar');
});

it('caches component types by modification date', function () {
    (new CompileTwigComponent('component-with-fields.twig', 'test/component-with-fields'))->handle();
    $hash = sha1('test/component-with-fields');
    $filemtime = filemtime(Craft::$app->getView()->resolveTemplate('component-with-fields.twig'));
    expect(App::parseEnv('@runtime/compiled_classes/ComponentType'.$hash.$filemtime.'.php'))->toBeFile();
})->skip();

it('does not re-cache when unchanged', function () {

})->todo();

it('re-caches on modification', function () {
    $oneHourAgo = (new \DateTime)->sub(new \DateInterval('P60M'))->getTimestamp();
    $now = (new \DateTime)->getTimestamp();

    $hash = sha1('test/component-with-fields');
    $filepath = Craft::$app->getView()->resolveTemplate('component-with-fields.twig');
    $touchCacheAt = function ($timestamp) use ($hash, $filepath) {
        GetFileMTime::mock($filepath, $timestamp);
        (new CompileTwigComponent('component-with-fields.twig', 'test/component-with-fields'))->handle();
        expect(App::parseEnv('@runtime/compiled_classes/ComponentType'.$hash.$timestamp.'.php'))->toBeFile();
    };

    $touchCacheAt($oneHourAgo);
    $touchCacheAt($now);

    expect(App::parseEnv('@runtime/compiled_classes/ComponentType'.$hash.$oneHourAgo.'.php'))->not->toBeFile();
})->skip();

it('gets field schema', function () {

})->todo();
