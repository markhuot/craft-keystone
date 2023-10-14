<?php

namespace markhuot\keystone\listeners;

use Craft;
use markhuot\keystone\twig\ExportExtension;

class RegisterTwigExtensions
{
    public function handle()
    {
        Craft::$app->getView()->registerTwigExtension(new ExportExtension);
    }
}
