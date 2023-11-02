<?php

namespace markhuot\keystone\base;

use yii\helpers\Html;

class CssRuleBag
{
    protected array $rules = [];

    public function addDeclaration($value, $key, $selector = null)
    {
        return $this->addRule($key.':'.$value, $selector);
    }

    public function addRule(string $rule, string $selector = null)
    {
        $hash = hash('sha1', $rule.$selector);

        if (isset($this->rules[$hash])) {
            return 'c'.array_search($hash, array_keys($this->rules));
        } else {
            $this->rules[$hash] = [
                'rule' => $rule,
                'selector' => $selector,
            ];

            return 'c'.(count($this->rules) - 1);
        }
    }

    public function getRules()
    {
        return $this->rules;
    }

    public function __toString()
    {
        return Html::style(collect($this->rules)
            ->values()
            ->map(function ($rule, $index) {
                $class = ".c{$index}";
                $selector = $rule['selector'] ?? '&';
                $selector = str_replace('&', $class, $selector);

                return "{$selector}{{$rule['rule']}}";
            })
            ->join("\n"));
    }
}
