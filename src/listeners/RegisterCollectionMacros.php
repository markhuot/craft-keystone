<?php

namespace markhuot\keystone\listeners;

use Illuminate\Support\Collection;

class RegisterCollectionMacros
{
    public function handle()
    {
        Collection::macro('filterRecursive', function (callable $callback = null) {
            if (! $callback) {
                $callback = fn ($value) => ! empty($value);
            }

            return $this
                ->filter(fn ($value, $key) => $callback($value, $key))
                ->map(fn ($value, $key) => is_array($value) ?
                    collect($value)->filterRecursive($callback)->toArray() :
                    $value);
        });

        Collection::macro('mapIntoSpread', function (string $className) {
            return $this->map(function ($item) use ($className) {
                return new $className(...$item);
            });
        });

        Collection::macro('mapKey', function (string $givenKey, callable $callback) {
            return $this->map(function ($item, $key) use ($givenKey, $callback) {
                return $givenKey === $key ? $callback($item, $key) : $item;
            });
        });
    }
}
