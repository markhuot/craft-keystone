<?php

namespace markhuot\keystone\attributes;

use Illuminate\Support\Collection;
use markhuot\keystone\base\Attribute;

class Size extends Attribute
{
    public function __construct(
        protected ?array $value = []
    ) {
    }

    public function getInputHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate('keystone/attributes/size', [
            'name' => get_class($this),
            'value' => $this->value ?? null,
        ]);
    }

    public function getCssRules(): Collection
    {
        return collect($this->value)
            ->filter();
    }
}
