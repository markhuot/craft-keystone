<?php

namespace markhuot\keystone\listeners;

use craft\events\DefineBehaviorsEvent;
use markhuot\keystone\behaviors\RenderFieldHtmlBehavior;

class AttachFieldHtmlBehavior
{
    public function handle(DefineBehaviorsEvent $event)
    {
        $event->behaviors[] = RenderFieldHtmlBehavior::class;
    }
}
