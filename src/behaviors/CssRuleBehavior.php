<?php

namespace markhuot\keystone\behaviors;

use craft\web\View;
use markhuot\keystone\base\CssRuleBag;
use yii\base\Behavior;
use yii\helpers\Html;

/**
 * @property View $owner;
 */
class CssRuleBehavior extends Behavior
{
    public function registerCssRule(string $rule, string $selector = null)
    {
        $this->owner->css['__cssRules'] ??= new CssRuleBag;

        return $this->owner->css['__cssRules']->addRule($rule, $selector);
    }

    public function registerCssDeclaration(string $value, string $key, string $selector = null)
    {
        $this->owner->css['__cssRules'] ??= new CssRuleBag;

        return $this->owner->css['__cssRules']->addDeclaration($value, $key, $selector);
    }

    public function clearCssRules()
    {
        unset($this->owner->css['__cssRules']);

        return $this;
    }

    public function getCssRules()
    {
        $this->owner->css['__cssRules'] ??= new CssRuleBag;

        return $this->owner->css['__cssRules']->getRules() ?? null;
    }
}
