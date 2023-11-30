<?php

namespace markhuot\keystone\listeners;

use markhuot\keystone\behaviors\BodyParamObjectBehavior;
use markhuot\keystone\behaviors\CssRuleBehavior;

use function markhuot\keystone\helpers\base\app;

class AttachPerRequestBehaviors
{
    public function handle(): void
    {
        app()->getRequest()->attachBehaviors(['bodyParamObject' => BodyParamObjectBehavior::class]);

        app()->getView()->attachBehaviors(['cssRules' => CssRuleBehavior::class]);
        app()->getView()->clearCssRules();
    }
}
