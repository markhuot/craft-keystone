<?php

namespace markhuot\keystone\behaviors;

use Craft;
use craft\base\ElementInterface;
use yii\base\Behavior;

/**
 * @property ElementInterface $owner
 */
class ElementRefreshBehavior extends Behavior
{
    public function refresh()
    {
        return Craft::$app->getElements()->getElementById($this->owner->id);
    }
}
