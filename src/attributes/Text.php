<?php

namespace markhuot\keystone\attributes;

use Illuminate\Support\Collection;
use markhuot\keystone\base\Attribute;

class Text extends Attribute
{
    public function __construct(
        protected ?array $value = []
    ) {
    }

    public function getInputHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate('keystone/attributes/text', [
            'name' => get_class($this),
            'value' => $this->value,
        ]);
    }

    public function serialize(mixed $value): mixed
    {
        if (empty($value['color'])) {
            unset($value['color']);
        }

        return $value;
    }

    public function getCssRules(): Collection
    {
        return collect($this->value)

            // re-map everything to proper CSS rules
            ->mapWithKeys(fn ($value, $key) => match ($key) {
                // convert the color from Craft's hex format to rgb so we can set opacity
                'color' => ['color' => implode(' ', sscanf($value, '%02x%02x%02x'))],
                default => [$key => $value],
            })

            // merge rgb color and opacity to a single value
            ->mergeKeys(fn ($color, $alpha) => ['color' => 'rgb('.($color ?? '0 0 0').'/'.($alpha ?? '1').')']);
    }
}
