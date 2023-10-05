<?php

namespace markhuot\keystone\listeners;

use Craft;
use markhuot\keystone\behaviors\BodyParamObjectBehavior;

class AddBodyParamObjectBehavior
{
    public function handle(): void
    {
        Craft::$app->request->attachBehaviors(['bodyParamObject' => BodyParamObjectBehavior::class]);
    }
}
