<?php

namespace markhuot\keystone\listeners;

use markhuot\keystone\events\RegisterAttributeType;

class RegisterDefaultAttributeTypes
{
    public function handle(RegisterAttributeType $event): void
    {
        $event->add(\markhuot\keystone\attributes\Alignment::class);
        $event->add(\markhuot\keystone\attributes\Background::class);
        $event->add(\markhuot\keystone\attributes\Border::class);
        $event->add(\markhuot\keystone\attributes\Custom::class);
        $event->add(\markhuot\keystone\attributes\Display::class);
        $event->add(\markhuot\keystone\attributes\Grid::class);
        $event->add(\markhuot\keystone\attributes\Margin::class);
        $event->add(\markhuot\keystone\attributes\Padding::class);
        $event->add(\markhuot\keystone\attributes\Rotate::class);
        $event->add(\markhuot\keystone\attributes\Size::class);
        $event->add(\markhuot\keystone\attributes\SpaceBetween::class);
        $event->add(\markhuot\keystone\attributes\Text::class);
    }
}
