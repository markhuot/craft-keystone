<?php

namespace markhuot\keystone\listeners;

use markhuot\keystone\events\RegisterComponentTypes;

class RegisterDefaultComponentTypes
{
    public function handle(RegisterComponentTypes $event): void
    {
        $event->registerTwigTemplate('keystone/asset', 'cp:keystone/components/asset.twig');
        $event->registerTwigTemplate('keystone/entry', 'cp:keystone/components/entry.twig');
        $event->registerTwigTemplate('keystone/entryquery', 'cp:keystone/components/entryquery.twig');
        $event->registerTwigTemplate('keystone/fragment', 'cp:keystone/components/fragment.twig');
        $event->registerTwigTemplate('keystone/heading', 'cp:keystone/components/heading.twig');
        $event->registerTwigTemplate('keystone/icon', 'cp:keystone/components/icon.twig');
        $event->registerTwigTemplate('keystone/link', 'cp:keystone/components/link.twig');
        $event->registerTwigTemplate('keystone/section', 'cp:keystone/components/section.twig');
        $event->registerTwigTemplate('keystone/tab', 'cp:keystone/components/tab.twig');
        $event->registerTwigTemplate('keystone/tabs', 'cp:keystone/components/tabs.twig');
        $event->registerTwigTemplate('keystone/template', 'cp:keystone/components/template.twig');
        $event->registerTwigTemplate('keystone/text', 'cp:keystone/components/text.twig');
    }
}
