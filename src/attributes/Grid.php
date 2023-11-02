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
            ->mergeKeys(['grid-template-columns', 'grid-template-widths'], function ($cols, $widths) {
                $cols = collect(range(0, $cols - 1));

                return ['grid-template-columns' => $cols->map(fn ($index) => $widths[$index] ?? '1fr')->join(' ')];
            });
    }
}
