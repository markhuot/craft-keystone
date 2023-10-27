<?php

namespace markhuot\keystone\actions;

/**
 * Takes an array of either CSS "shorthand" or "expanded" values and returns a css-ish
 * representation of them.
 *
 * For example, given `->handle(['useExpanded' => false, 'shorthand' => 3], 'margin')` you
 * would end up with `['margin' => 3]`.
 *
 * If you `useExpanded` like this, `->handle(['useExpanded' => true, 'expanded' => ['top' => 3]], 'margin')`
 * then you would end up with `['margin-top' => 3]`.
 *
 * Notice that the `$property` attribute is automatically converted to `{$property}-top` or `{$property}-left`
 * for the expanded values. If you need to customize that you can pass in an `$expandedProperty` attribute
 * with `&` as a placeholder for the direction. E.g., `->handle([...], 'border', 'border-&-width')` would
 * return `['border-top-width' => 3]` if `'top' => 3` was passed.
 */
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
