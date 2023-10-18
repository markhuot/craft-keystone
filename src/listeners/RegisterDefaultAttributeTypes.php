<?php

namespace markhuot\keystone\listeners;

use markhuot\keystone\events\RegisterAttributeType;

class RegisterDefaultAttributeTypes
{
    public function handle(RegisterAttributeType $event)
    {
        $event->add(\markhuot\keystone\styles\Display::class);
        $event->add(\markhuot\keystone\styles\Border::class);
        $event->add(\markhuot\keystone\styles\Margin::class);
        $event->add(\markhuot\keystone\styles\Padding::class);
        $event->add(\markhuot\keystone\styles\Rotate::class);
        $event->add(\markhuot\keystone\styles\SpaceBetween::class);
    }
}
