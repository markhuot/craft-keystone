<?php

namespace markhuot\keystone\helpers\event;

use markhuot\keystone\interfaces\ListenerInterface;
use markhuot\keystone\interfaces\ShouldHandleEvents;
use ReflectionClass;
use ReflectionParameter;
use yii\base\Event;

use function markhuot\keystone\helpers\base\resolve;

/**
 * @param  array{class-string, string, class-string<ListenerInterface>}|callable():array{class-string, string, class-string<ListenerInterface>}  ...$events
 */
function listen(array|callable ...$events): void
{
    foreach ($events as $event) {
        try {
            if (is_callable($event)) {
                [$className, $eventName, $handlerClass] = $event();
            } else {
                [$className, $eventName, $handlerClass] = $event;
            }

            $handler = resolve($handlerClass);

            if (method_exists($handler, 'init')) {
                $handler->init();
            }

            Event::on($className, $eventName, function (...$args) use ($handler) {
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
