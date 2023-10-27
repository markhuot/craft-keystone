<?php

namespace markhuot\keystone\listeners;

use craft\events\RegisterComponentTypesEvent;
use markhuot\keystone\fields\Keystone;

class RegisterKeystoneFieldType
{
    public function handle(RegisterComponentTypesEvent $event): void
    {
        $event->types[] = Keystone::class;
    }
}
