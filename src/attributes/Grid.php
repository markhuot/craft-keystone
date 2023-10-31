<?php

namespace markhuot\keystone\attributes;

use Illuminate\Support\Collection;
use markhuot\keystone\base\Attribute;

class Grid extends Attribute
{
    public function __construct(
        protected ?array $value = []
    ) {
    }

    public function getInputHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate('keystone/attributes/grid', [
            'name' => get_class($this),
            'value' => $this->value,
        ]);
    }

    public function getCssRules(): Collection
    {
        return collect($this->value)
            ->mapWithKeys(fn ($value, $key) => match ($key) {
                'grid-template-columns' => [$key => 'repeat('.$value.', minmax(0, 1fr))'],
                default => [$key => $value],
            });
    }
}
