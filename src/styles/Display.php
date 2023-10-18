<?php

namespace markhuot\keystone\styles;

use craft\helpers\Cp;
use markhuot\keystone\base\Style;
use Twig\Markup;

class Display extends Style
{
    public function __construct(
        protected ?string $value=''
    ) {
    }

    public function getInputHtml(): string
    {
        return Cp::selectFieldHtml([
            'label' => 'Display',
            'name' => get_class($this),
            'options' => [
                ['label' => '(default)', 'value' => ''],
                ['label' => 'Block', 'value' => 'block'],
                ['label' => 'Inline', 'value' => 'inline'],
                ['label' => 'Flex', 'value' => 'flex'],
                ['label' => 'Grid', 'value' => 'grid'],
            ],
            'value' => $this->value,
        ]);
    }

    public function toAttributeArray(): array
    {
        return ['class' => $this->value];
    }
}
