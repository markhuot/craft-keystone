<?php

namespace markhuot\keystone\attributes;

use craft\helpers\Cp;
use markhuot\keystone\base\Attribute;
use Twig\Markup;

class Background extends Attribute
{
    public function __construct(
        protected ?array $value = []
    ) {
    }

    public function getInputHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate('keystone/styles/color', [
            'label' => 'Background',
            'name' => get_class($this),
            'value' => $this->value ?? null,
        ]);
    }

    public function toAttributeArray(): array
    {
        return ['class' => 'bg-[#' . ($this->value['color'] ?? 'inherit') . ']'];
    }
}
