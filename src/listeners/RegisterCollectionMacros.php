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

        Collection::macro('mergeKeys', function ($callback) {
            /** @var Collection<array-key, mixed> $this */
            $values = [];
            $reflect = new \ReflectionFunction($callback);

            $found = false;
            foreach ($reflect->getParameters() as $parameter) {
                if ($this->has($parameter->name)) {
                    $found = true;
                }

                $values[$parameter->name] = $this->get($parameter->name, null);
            }

            if (! $found) {
                return $this;
            }

            return $this->merge($callback(...$values));
        });
    }
}
