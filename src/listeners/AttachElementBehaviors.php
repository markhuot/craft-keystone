<?php

namespace markhuot\keystone\listeners;

use craft\events\DefineBehaviorsEvent;
use markhuot\keystone\behaviors\ElementRefreshBehavior;
use markhuot\keystone\behaviors\RenderFieldHtmlBehavior;

class AttachElementBehaviors
{
    public function handle(DefineBehaviorsEvent $event): void
    {
        $event->behaviors[] = RenderFieldHtmlBehavior::class;
        $event->behaviors[] = ElementRefreshBehavior::class;
    }
}
