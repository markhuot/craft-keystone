<?php

namespace markhuot\keystone\styles;

use craft\helpers\Cp;
use markhuot\keystone\base\Attribute;
use Twig\Markup;

class Width extends Attribute
{
    public function __construct(
        protected ?string $value = null
    ) { }

    public function getInputHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate('keystone/styles/slider', [
            'label' => 'Width (px)',
            'name' => get_class($this),
            'value' => $this->value ?? null,
            'min' => 0,
            'max' => 1000,
        ]);
    }

    public function toAttributeArray(): array
    {
        return ['class' => $this->value ? 'w-[' . $this->value . 'px]' : null];
    }
}
