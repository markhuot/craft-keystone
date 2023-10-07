<?php

namespace markhuot\keystone\listeners;

use markhuot\keystone\base\ComponentType;
use markhuot\keystone\events\RegisterComponentTypes;

class RegisterDefaultComponentTypes
{
    public function handle(RegisterComponentTypes $event)
    {
        // @todo pass in type
        // $event->types[] = ['keystone/fragment' => 'keystone/components/fragment.twig'];

         $event->registerTwigTemplate('keystone/fragment', 'cp:keystone/components/fragment.twig');
         $event->registerTwigTemplate('keystone/section', 'cp:keystone/components/section.twig');
         $event->registerTwigTemplate('keystone/heading', 'cp:keystone/components/heading.twig');
         $event->registerTwigTemplate('keystone/text', 'cp:keystone/components/text.twig');
         $event->registerTwigTemplate('keystone/asset', 'cp:keystone/components/asset.twig');

        // $event->types[] = new class extends ComponentType { protected string $type = 'keystone/fragment'; };
        // $event->types[] = new class extends ComponentType { protected string $type = 'keystone/section'; };
        // $event->types[] = new class extends ComponentType { protected string $type = 'keystone/heading'; };
        // $event->types[] = new class extends ComponentType { protected string $type = 'keystone/text'; };
        // $event->types[] = new class extends ComponentType { protected string $type = 'keystone/asset'; };
    }
}
