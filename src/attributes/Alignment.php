<?php

namespace markhuot\keystone\attributes;

use Illuminate\Support\Collection;
use markhuot\keystone\base\Attribute;

class Alignment extends Attribute
{
    public function __construct(
        protected ?array $value = []
    ) {
    }

    public function getInputHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate('keystone/styles/alignment', [
            'name' => get_class($this),
            'value' => $this->value,
        ]);
    }

    public function getCssRules(): Collection
    {
        return collect($this->value)

            // re-map everything to proper CSS rules
            ->mapWithKeys(fn ($value, $key) => match ($key) {
                default => [$key => $value],
            });
    }
}
