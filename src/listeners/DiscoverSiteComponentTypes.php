<?php

namespace markhuot\keystone\listeners;

use craft\helpers\App;
use markhuot\keystone\events\RegisterComponentTypes;

class DiscoverSiteComponentTypes
{
    public function handle(RegisterComponentTypes $event)
    {
        $componentsDirectory = App::parseEnv('@templates/components');
        $templates = [
            ...glob($componentsDirectory.'/*.twig'),
            ...glob($componentsDirectory.'/**/*.twig'),
        ];

        foreach ($templates as $template) {
            $start = mb_strlen(App::parseEnv('@templates/'));
            $localPath = 'site:' . substr($template, $start);
            $key = 'site/'.substr($template, $start, -5);
            $event->registerTwigTemplate($key, $localPath);
        }
    }
}
