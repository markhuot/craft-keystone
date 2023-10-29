<?php

namespace markhuot\keystone\listeners;

use craft\events\DefineBehaviorsEvent;
use markhuot\keystone\behaviors\InteractsWithKeystoneBehavior;

class AttachFieldBehavior
{
    const INTERACTS_WITH_KEYSTONE = 'interactsWithKeystone';

    public function handle(DefineBehaviorsEvent $event): void
    {
        $event->behaviors[self::INTERACTS_WITH_KEYSTONE] = InteractsWithKeystoneBehavior::class;
    }
}
