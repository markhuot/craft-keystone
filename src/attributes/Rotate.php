<?php

namespace markhuot\keystone\attributes;

use Illuminate\Support\Collection;
use markhuot\keystone\base\Attribute;

class Rotate extends Attribute
{
    public function __construct(
        protected ?string $value = null
    ) {
    }

    public function getInputHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate('keystone/attributes/slider', [
            'label' => 'Rotate',
            'name' => get_class($this),
            'value' => $this->value ?? null,
            'min' => -360,
            'max' => 360,
        ]);
    }

    public function getCssRules(): Collection
    {
        return collect(['transform' => $this->value])
            ->filter()
            ->map(fn ($value) => 'rotateZ('.$value.'deg)');
    }
}
