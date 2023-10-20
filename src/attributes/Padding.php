<?php

namespace markhuot\keystone\attributes;

use craft\helpers\Cp;
use markhuot\keystone\base\Attribute;
use Twig\Markup;

class Padding extends Attribute
{
    public function __construct(
        protected ?array $value = null
    ) { }

    public function getInputHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate('keystone/styles/margin', [
            'label' => 'Padding',
            'name' => get_class($this),
            'value' => $this->value ?? null,
        ]);
    }

    public function toAttributeArray(): array
    {
        if ($this->value['useExpanded'] ?? false) {
            return [
                'class' => implode(' ', array_filter([
                    !empty($this->value['expanded']['t']) ? 'pt-[' . $this->value['expanded']['t'] . ']': null,
                    !empty($this->value['expanded']['r']) ? 'pr-[' . $this->value['expanded']['r'] . ']': null,
                    !empty($this->value['expanded']['b']) ? 'pb-[' . $this->value['expanded']['b'] . ']': null,
                    !empty($this->value['expanded']['l']) ? 'pl-[' . $this->value['expanded']['l'] . ']': null,
                ])),
            ];
        }

        if ($this->value['shorthand'] ?? false) {
            return ['class' => 'p-[' . $this->value['shorthand'] . ']'];
        }

        return [];
    }
}
