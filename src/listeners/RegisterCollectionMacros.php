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

        Collection::macro('forgetWhen', function (mixed $keys, mixed $condition) {
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

        // Collection::macro('load', function (array $data) {
        //     foreach ($data as $key => $value) {
        //         $this->set($key, $value);
        //     }
        //
        //     return $this;
        // });

        // Collection::macro('loadWhenMissing', function (array $data) {
        //     return $this->loadWhen($data, fn ($value, $key, $collection) => ! $collection->has($key));
        // });

        // Collection::macro('loadWhenEmpty', function (array $data) {
        //     return $this->loadWhen($data, fn ($value, $key, $collection) => empty($collection->get($key)));
        // });

        // Collection::macro('loadWhen', function ($data, $callback) {
        //     foreach ($data as $key => $value) {
        //         if ($callback($value, $key, $this)) {
        //             $this->set($key, $value);
        //         }
        //     }
        //
        //     return $this;
        // });

        Collection::macro('mergeKeys', function ($callback) {
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
