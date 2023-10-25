<?php

namespace markhuot\keystone\attributes;

use craft\helpers\Cp;
use Illuminate\Support\Collection;
use markhuot\keystone\base\Attribute;

class Display extends Attribute
{
    public function __construct(
        protected ?string $value = ''
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
                ['label' => 'Inline block', 'value' => 'inline-block'],
                ['label' => 'Inline', 'value' => 'inline'],
                ['label' => 'Flex', 'value' => 'flex'],
                ['label' => 'Grid', 'value' => 'grid'],
            ],
            'value' => $this->value,
        ]);
    }

    public function getCssRules(): Collection
    {
        return collect(['display' => $this->value])
            ->filter();
    }
}
