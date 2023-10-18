<?php

namespace markhuot\keystone\actions;

use craft\base\Event;
use markhuot\keystone\events\RegisterAttributeType;

class GetAttributeTypes
{
    const EVENT_REGISTER_ATTRIBUTE_TYPE = 'registerKeystoneAttributeType';

    public function handle()
    {
        $event = new RegisterAttributeType;
        Event::trigger(static::class, static::EVENT_REGISTER_ATTRIBUTE_TYPE, $event);

        return $event->getTypes();
    }
}
