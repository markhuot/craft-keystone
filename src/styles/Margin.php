<?php

namespace markhuot\keystone\styles;

use craft\helpers\Cp;
use markhuot\keystone\base\Attribute;
use Twig\Markup;

class Margin extends Attribute
{
    public function __construct(
        protected ?array $value = null
    ) { }

    public function getInputHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate('keystone/styles/margin', [
            'label' => 'Margin',
            'name' => get_class($this),
            'value' => $this->value ?? null,
        ]);
    }

    public function toAttributeArray(): array
    {
        if ($this->value['useExpanded'] ?? false) {
            return [
                'class' => implode(' ', array_filter([
                    !empty($this->value['expanded']['t']) ? 'mt-[' . $this->value['expanded']['t'] . ']': null,
                    !empty($this->value['expanded']['r']) ? 'mr-[' . $this->value['expanded']['r'] . ']': null,
                    !empty($this->value['expanded']['b']) ? 'mb-[' . $this->value['expanded']['b'] . ']': null,
                    !empty($this->value['expanded']['l']) ? 'ml-[' . $this->value['expanded']['l'] . ']': null,
                ])),
            ];
        }

        if ($this->value['shorthand'] ?? false) {
            return ['class' => 'm-[' . $this->value['shorthand'] . ']'];
        }

        return [];
    }
}
