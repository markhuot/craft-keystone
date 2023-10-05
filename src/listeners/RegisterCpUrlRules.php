<?php

namespace markhuot\keystone\listeners;

use craft\events\RegisterUrlRulesEvent;

class RegisterCpUrlRules
{
    public function handle(RegisterUrlRulesEvent $event)
    {
        $routes = include __DIR__.'/../../src/config/routes.php';

        foreach ($routes as $route => $config) {
            $event->rules[$route] = $config;
        }
    }
}
