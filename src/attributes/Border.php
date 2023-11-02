<?php

namespace markhuot\keystone\attributes;

use Illuminate\Support\Collection;
use markhuot\keystone\actions\MapExpandedAttributeValue;
use markhuot\keystone\base\Attribute;

class Border extends Attribute
{
    public function __construct(
        protected ?array $value = []
    ) {
    }

    public function getInputHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate('keystone/attributes/border', [
            'label' => 'Border Radius',
            'name' => get_class($this),
            'value' => $this->value ?? null,
        ]);
    }

    public function getCssRules(): Collection
    {
        return collect($this->value)
            ->forgetWhen(['color', 'radius'], fn ($value) => empty($value))
            ->mapWithKeys(fn ($value, $key) => match ($key) {
                'color' => ['border-color' => '#'.$value],
                'width' => (new MapExpandedAttributeValue)->handle($value, 'border-width', 'border-&-width'),
                default => ['border-'.$key => $value],
            })
            ->pipe(function (Collection $collection) {
                $isStyled = $collection->only(['border-color', 'border-width', 'border-top-width', 'border-right-width', 'border-bottom-width', 'border-left-width'])->isNotEmpty();
                if (! $isStyled) {
                    $collection->forget(['border-style']);
                }

                return $collection;
            })
            ->pipe(function (Collection $collection) {
                $widths = $collection->only(['border-top-width', 'border-right-width', 'border-bottom-width', 'border-left-width']);
                if ($widths->isEmpty()) {
                    return $collection;
                }

                $widths = $widths->flip()->map(fn ($key) => str_replace('width', 'style', $key))->flip();
                $style = $collection->get('border-style');
                $widths = $widths->map(fn ($_) => $style);

                return $collection->merge($widths)->forget(['border-style']);
            })
            ;
    }
}
