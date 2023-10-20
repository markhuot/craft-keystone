<?php

namespace markhuot\keystone\attributes;

use craft\helpers\Cp;
use markhuot\keystone\base\Attribute;
use Twig\Markup;

class Border extends Attribute
{
    public function __construct(
        protected ?array $value = []
    ) {
    }

    public function getInputHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate('keystone/styles/border', [
            'label' => 'Border Radius',
            'name' => get_class($this).'[borderRadius]',
            'value' => $this->value['borderRadius'] ?? null,
        ]);
    }

    public function toAttributeArray(): array
    {
        $rounded = match ($this->value['borderRadius'] ?? null) {
            null => '',
            '100%' => 'rounded-full',
            default => 'rounded-['.$this->value['borderRadius'].']',
        };

        return ['class' => $rounded];
    }
}
