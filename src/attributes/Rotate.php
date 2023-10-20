<?php

namespace markhuot\keystone\attributes;

use craft\helpers\Cp;
use markhuot\keystone\base\Attribute;
use Twig\Markup;

class Rotate extends Attribute
{
    public function __construct(
        protected ?string $value = null
    ) { }

    public function getInputHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate('keystone/styles/slider', [
            'label' => 'Rotate',
            'name' => get_class($this),
            'value' => $this->value ?? null,
            'min' => -360,
            'max' => 360,
        ]);
    }

    public function toAttributeArray(): array
    {
        return ['class' => $this->value ? 'rotate-[' . $this->value . 'deg]' : null];
    }
}
