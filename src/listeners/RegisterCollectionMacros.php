<?php

namespace markhuot\keystone\listeners;

use Illuminate\Support\Collection;

class RegisterCollectionMacros
{
    public function handle(): void
    {
        Collection::macro('mapIntoSpread', function (string $className) {
            /** @var Collection<array-key, mixed> $this */
            return $this->map(function ($item) use ($className) {
                return new $className(...$item);
            });
        });

        Collection::macro('mapKey', function (string $givenKey, callable $callback) {
            /** @var Collection<array-key, mixed> $this */
            return $this->map(function ($item, $key) use ($givenKey, $callback) {
                return $givenKey === $key ? $callback($item, $key) : $item;
            });
        });

        Collection::macro('forgetWhen', function (mixed $keys, mixed $condition) {
            /** @var Collection<array-key, mixed> $this */
            $keysToCheck = is_array($keys) ? $keys : [$keys];

            return $this->filter(function ($value, $key) use ($keysToCheck, $condition) {
                if (! in_array($key, $keysToCheck)) {
                    return true;
                }

                if (is_callable($condition)) {
                    return $condition($value, $key);
                }

                return $value !== $condition;
            });
        });

        Collection::macro('mergeKeys', function (array $keys, $callback) {
            /** @var Collection<array-key, mixed> $this */
            $values = [];

            $found = false;
            foreach ($keys as $key) {
                if ($this->has($key)) {
                    $found = true;
                }

                $values[$key] = $this->get($key);
            }

            if (! $found) {
                return $this;
            }

            return $this->forget($keys)->merge($callback(...array_values($values)));
        });
    }
}
