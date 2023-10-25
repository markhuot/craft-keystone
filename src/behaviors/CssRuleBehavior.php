<?php

namespace markhuot\keystone\behaviors;

use craft\web\View;
use yii\base\Behavior;
use yii\helpers\Html;

/**
 * @property View $owner;
 */
class CssRuleBehavior extends Behavior
{
    public function registerCssRule(string $value, string $key, string $selector = null)
    {
        $this->owner->css['__cssRules'] ??= new class
        {
            protected array $rules = [];

            public function addRule($value, $key, $selector = null)
            {
                $hash = hash('sha1', $value.$key.$selector);
                if (isset($this->rules[$hash])) {
                    return 'c'.array_search($hash, array_keys($this->rules));
                } else {
                    $this->rules[$hash] = [
                        'property' => $key,
                        'value' => $value,
                        'selector' => $selector,
                    ];

                    return 'c'.(count($this->rules) - 1);
                }
            }

            public function __toString()
            {
                return Html::style(collect($this->rules)
                    ->values()
                    ->map(function ($rule, $index) {
                        $class = ".c{$index}";
                        $selector = $rule['selector'] ?? '&';
                        $selector = str_replace('&', $class, $selector);

                        return "{$selector}{{$rule['property']}:{$rule['value']}}";
                    })
                    ->join("\n"));
            }
        };

        return $this->owner->css['__cssRules']->addRule($value, $key, $selector);
    }

    public function getCssRules()
    {
        return $this->owner->css['__cssRules'] ?? null;
    }
}
