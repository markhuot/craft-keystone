<?php

namespace markhuot\keystone\listeners;

use markhuot\keystone\twig\KeystoneExtension;

use function markhuot\keystone\helpers\base\app;

class RegisterTwigExtensions
{
    public function handle(): void
    {
        app()->getView()->registerTwigExtension(new KeystoneExtension);
    }
}
