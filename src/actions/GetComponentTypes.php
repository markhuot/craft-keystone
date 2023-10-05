<?php

namespace markhuot\keystone\actions;

use craft\base\Event;
use markhuot\keystone\events\RegisterComponentTypes;

class GetComponentTypes
{
    const EVENT_REGISTER_COMPONENT_TYPES = 'registerKeystoneComponentTypes';

    public function handle()
    {
        $event = new RegisterComponentTypes;
        Event::trigger(static::class, static::EVENT_REGISTER_COMPONENT_TYPES, $event);

        return collect($event->types);
    }
}
