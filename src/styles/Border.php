<?php

namespace markhuot\keystone\styles;

use craft\helpers\Cp;
use markhuot\keystone\base\Style;
use Twig\Markup;

class Border extends Style
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
