<?php

namespace markhuot\keystone\attributes;

use Illuminate\Support\Collection;
use markhuot\keystone\base\Attribute;

class Custom extends Attribute
{
    public function __construct(
        protected ?string $value = ''
    ) {
    }

    public function getInputHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate('keystone/attributes/custom', [
            'name' => get_class($this),
            'value' => $this->value,
        ]);
    }

    public function toAttributeArray(): array
    {
        $className = \Craft::$app->getView()->registerCssRule($this->value);

        return ['class' => $className];
    }
}
