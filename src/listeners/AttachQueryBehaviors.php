<?php

namespace markhuot\keystone\listeners;

use craft\events\DefineBehaviorsEvent;
use markhuot\keystone\behaviors\QueryBehavior;

class AttachQueryBehaviors
{
    public function handle(DefineBehaviorsEvent $event)
    {
        $event->behaviors['keystoneQueryBehavior'] = QueryBehavior::class;
    }
}
