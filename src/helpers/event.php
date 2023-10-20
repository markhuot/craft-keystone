<?php

namespace markhuot\keystone\helpers\event;

use markhuot\craftai\listeners\ListenerInterface;
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

            Event::on($class, $event, fn (...$args) => \Craft::$container->invoke($handler->handle(...), $args));
        } catch (\Throwable $e) {
            if (preg_match('/Class ".+" not found/', $e->getMessage())) {
                continue;
            }

            throw $e;
        }
    }
}
