<?php

namespace markhuot\keystone\attributes;

use Illuminate\Support\Collection;
use markhuot\keystone\base\Attribute;

class Border extends Attribute
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

    public function getCssRules(): Collection
    {
        return collect($this->value)
            ->mapWithKeys(fn ($value, $key) => match ($key) {
                'borderRadius' => ['border-radius' => $value],
            });
    }
}
