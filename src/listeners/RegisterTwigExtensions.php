<?php

namespace markhuot\keystone\listeners;

use Craft;
use markhuot\keystone\twig\KeystoneExtension;

class RegisterTwigExtensions
{
    public function handle()
    {
        Craft::$app->getView()->registerTwigExtension(new KeystoneExtension);
    }
}
