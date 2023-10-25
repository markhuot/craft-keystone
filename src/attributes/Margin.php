<?php

namespace markhuot\keystone\attributes;

use Illuminate\Support\Collection;
use markhuot\keystone\base\Attribute;

class Margin extends Attribute
{
    public function __construct(
        protected ?array $value = null
    ) {
    }

    public function getInputHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate('keystone/styles/margin', [
            'label' => 'Margin',
            'name' => get_class($this),
            'value' => $this->value ?? null,
        ]);
    }

    public function getCssRules(): Collection
    {
        if ($this->value['useExpanded'] ?? false) {
            return collect($this->value['expanded'])
                ->mapWithKeys(fn ($value, $key) => ['margin-'.$key => $value])
                ->filter();
        } else {
            return collect(['margin' => $this->value['shorthand']])
                ->filter();
        }
    }
}
