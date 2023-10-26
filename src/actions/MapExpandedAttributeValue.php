<?php

namespace markhuot\keystone\actions;

class MapExpandedAttributeValue
{
    public function handle(?array $value, string $property, string $expandedProperty = null)
    {
        if ($expandedProperty === null) {
            $expandedProperty = "{$property}-&";
        }

        if ($value['useExpanded'] ?? false) {
            return collect($value['expanded'])
                ->mapWithKeys(fn ($value, $key) => [str_replace('&', $key, $expandedProperty) => $value])
                ->filter();
        } else {
            return collect([$property => $value['shorthand'] ?? null])
                ->filter();
        }
    }
}
