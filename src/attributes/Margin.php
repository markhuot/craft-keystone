<?php

namespace markhuot\keystone\attributes;

use Illuminate\Support\Collection;
use markhuot\keystone\actions\MapExpandedAttributeValue;
use markhuot\keystone\base\Attribute;

class Margin extends Attribute
{
    public function __construct(
        protected ?array $value = null
    ) {
    }

    public function getInputHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate('keystone/attributes/margin', [
            'label' => 'Margin',
            'name' => get_class($this),
            'value' => $this->value ?? null,
        ]);
    }

    public function getCssRules(): Collection
    {
        return (new MapExpandedAttributeValue)->handle($this->value, 'margin');
    }
}
