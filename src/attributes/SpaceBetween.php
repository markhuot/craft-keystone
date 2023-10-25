<?php

namespace markhuot\keystone\attributes;

use markhuot\keystone\base\Attribute;

class SpaceBetween extends Attribute
{
    public function __construct(
        protected ?array $value = ['x' => null, 'y' => null],
    ) {
    }

    public function getInputHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate('keystone/styles/space-between', [
            'label' => 'Space Between',
            'name' => get_class($this),
            'value' => $this->value ?? null,
        ]);
    }

    public function toAttributeArray(): array
    {
        $classNames = collect($this->value)
            ->filter()
            ->mapWithKeys(fn ($value, $key) => match ($key) {
                'x' => ['margin-left' => $value],
                'y' => ['margin-top' => $value],
            })
            ->map(fn ($value, $key) => \Craft::$app->getView()->registerCssRule($value, $key, '& > * + *'));

        return array_filter(['class' => $classNames->join(' ')]);
    }
}
