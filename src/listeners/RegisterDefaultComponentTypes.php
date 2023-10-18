<?php

namespace markhuot\keystone\listeners;

use markhuot\keystone\events\RegisterComponentTypes;

class RegisterDefaultComponentTypes
{
    public function handle(RegisterComponentTypes $event)
    {
        $event->registerTwigTemplate('keystone/fragment', 'cp:keystone/components/fragment.twig');
        $event->registerTwigTemplate('keystone/section', 'cp:keystone/components/section.twig');
        $event->registerTwigTemplate('keystone/heading', 'cp:keystone/components/heading.twig');
        $event->registerTwigTemplate('keystone/text', 'cp:keystone/components/text.twig');
        $event->registerTwigTemplate('keystone/asset', 'cp:keystone/components/asset.twig');
        $event->registerTwigTemplate('keystone/link', 'cp:keystone/components/link.twig');
    }
}
