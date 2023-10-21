<?php

namespace markhuot\keystone\attributes;

use Craft;
use markhuot\keystone\base\Attribute;
use Twig\Markup;

class Text extends Attribute
{
    public function __construct(
        protected ?array $value = []
    ) { }

    public function getInputHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate('keystone/styles/text', [
            'name' => get_class($this),
            'value' => $this->value,
        ]);
    }

    public function toAttributeArray(): array
    {
        $color = ($this->value['color'] ?? false) ? '#' . $this->value['color'] : 'inherit';
        $alpha = $this->value['alpha'] ?? '1';

        return ['class' => 'text-[' . $color . ']/[' . $alpha . '] text-'.($this->value['align'] ?? 'left')];
    }
}
