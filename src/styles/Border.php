<?php

namespace markhuot\keystone\styles;

use craft\helpers\Cp;
use markhuot\keystone\base\Style;
use Twig\Markup;

class Border extends Style
{
    public function __construct(
        protected ?array $value = []
    ) { }

    public function getInputHtml(): Markup
    {
        return new Markup(Cp::textFieldHtml([
            'label' => 'Border Radius',
            'name' => get_class($this).'[borderRadius]',
            'value' => $this->value['borderRadius'] ?? null,
        ]), 'utf-8');
    }

    public function toAttributeArray(): array
    {
        $rounded = match ($this->value['borderRadius'] ?? null) {
            null => '',
            '100%' => 'rounded-full',
            default => 'rounded-[' . $this->value['borderRadius'] . ']',
        };
        return ['class' => $rounded];
    }

    public function serialize($value)
    {

    }
}
