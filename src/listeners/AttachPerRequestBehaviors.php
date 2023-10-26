<?php

namespace markhuot\keystone\listeners;

use Craft;
use markhuot\keystone\behaviors\BodyParamObjectBehavior;
use markhuot\keystone\behaviors\CssRuleBehavior;

class AttachPerRequestBehaviors
{
    public function handle(): void
    {
        Craft::$app->getRequest()->attachBehaviors(['bodyParamObject' => BodyParamObjectBehavior::class]);

        Craft::$app->getView()->attachBehaviors(['cssRules' => CssRuleBehavior::class]);
        Craft::$app->getView()->clearCssRules();
    }
}
