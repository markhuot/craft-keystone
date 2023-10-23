<?php

namespace markhuot\keystone\attributes;

use markhuot\keystone\base\Attribute;

class Size extends Attribute
{
    public function __construct(
        protected ?array $value = []
    ) {
    }

    public function getInputHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate('keystone/styles/size', [
            'name' => get_class($this),
            'value' => $this->value ?? null,
        ]);
    }

    public function toAttributeArray(): array
    {
        return ['class' => implode(' ', array_filter([
            $this->value['width'] ?? false ? 'w-['.$this->value['width'].']' : '',
            $this->value['height'] ?? false ? 'h-['.$this->value['height'].']' : '',
        ]))];
    }
}
