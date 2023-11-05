<?php

namespace markhuot\keystone\helpers\event;

use markhuot\craftai\listeners\ListenerInterface;
use markhuot\keystone\interfaces\ShouldHandleEvents;
use ReflectionClass;
use ReflectionParameter;
use yii\base\Event;

/**
 * @param  array|callable  ...$events
 */
function listen(...$events): void
{
    /** @var array|callable(): array{0: string, 1: string, 2: class-string} $event */
    foreach ($events as $event) {
        try {
            if (is_callable($event)) {
                [$class, $event, $handlerClass] = $event();
            } else {
                [$class, $event, $handlerClass] = $event;
            }

            /** @var ListenerInterface $handler */
            $handler = \Craft::$container->get($handlerClass);

            if (method_exists($handler, 'init')) {
                $handler->init();
            }

            Event::on($class, $event, function (...$args) use ($handler) {
                $reflect = new ReflectionClass($handler);
                if ($reflect->implementsInterface(ShouldHandleEvents::class)) {
                    $method = $reflect->getMethod('handle');
                    $args = collect($method->getParameters())
                        ->map(fn (ReflectionParameter $param) => $args[0]->{$param->getName()} ?? null)
                        ->filter()
                        ->all();

                }

                return \Craft::$container->invoke($handler->handle(...), $args);
            });
        } catch (\Throwable $e) {
            if (preg_match('/Class ".+" not found/', $e->getMessage())) {
                continue;
            }

            throw $e;
        }
    }
}
