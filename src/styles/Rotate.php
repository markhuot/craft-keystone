<?php

namespace markhuot\keystone\styles;

use craft\helpers\Cp;
use markhuot\keystone\base\Style;
use Twig\Markup;

class Rotate extends Style
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
