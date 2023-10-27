<?php

namespace markhuot\keystone\listeners;

use markhuot\keystone\events\RegisterComponentTypes;

use function markhuot\keystone\helpers\base\parseEnv;

class DiscoverSiteComponentTypes
{
    public function handle(RegisterComponentTypes $event): void
    {
        $templatesPath = parseEnv('@templates/');
        $componentsDirectory = $templatesPath.'components/';
        $templates = [
            ...(glob($componentsDirectory.'*.twig') ?: []),
            ...(glob($componentsDirectory.'**/*.twig') ?: []),
        ];

        foreach ($templates as $template) {
            $start = mb_strlen($templatesPath);
            $localPath = 'site:'.substr($template, $start);
            $key = 'site/'.substr($template, $start, -5);
            $event->registerTwigTemplate($key, $localPath);
        }
    }
}
