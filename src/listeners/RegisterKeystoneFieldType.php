<?php

namespace markhuot\keystone\listeners;

use craft\events\RegisterComponentTypesEvent;
use markhuot\keystone\fields\Keystone;

class RegisterKeystoneFieldType
{
    function handle(RegisterComponentTypesEvent $event)
    {
        $event->types[] = Keystone::class;
    }
}
