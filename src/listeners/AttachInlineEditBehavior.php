<?php

namespace markhuot\keystone\listeners;

use craft\events\DefineBehaviorsEvent;
use markhuot\keystone\behaviors\InlineEditBehavior;

class AttachInlineEditBehavior
{
    public function handle(DefineBehaviorsEvent $event): void
    {
        $event->behaviors['inlineEdit'] = InlineEditBehavior::class;
    }
}
