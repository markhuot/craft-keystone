<?php

namespace markhuot\keystone\base;

use Illuminate\Support\Collection;

class AttributeBag implements \ArrayAccess
{
    /** @var Collection<class-string<Attribute>, mixed> */
    protected Collection $attributes;

    public function __construct(Collection|array|null $attributes = [])
    {
        if (is_null($attributes)) {
            $attributes = [];
        }

        if (is_array($attributes)) {
            $attributes = collect($attributes);
        }

        $this->attributes = $attributes;
    }

    public function merge(array $new = []): self
    {
        $this->attributes = $this->attributes->mergeRecursive($new);

        return $this;
    }

    public function mergeDefaults(array $defaults = []): self
    {
        $this->attributes = collect($defaults)->merge($this->attributes);

        return $this;
    }

    /**
     * @return array<string>
     */
    public function toArray(): array
    {
        return $this->attributes
            ->map(function ($value, $key) {
                if (class_exists($key)) {
                    return (new $key($value))->toAttributeArray();
                }

                return [$key => $value];
            })
            ->reduce(fn ($attr, $carry) => array_merge_recursive($carry, $attr), []);
    }

    public function toHtml()
    {
        $attributes = $this->toArray();

        return collect($attributes)
            ->map(function ($value, $key) {
                if (is_array($value)) {
                    $value = implode(' ', $value);
                }

                return "{$key}=\"{$value}\"";
            })
            ->join(' ');
    }

    public function __toString()
    {
        return $this->toHtml();
    }

    public function offsetExists(mixed $offset): bool
    {
        return true;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributes->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->attributes->put($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->attributes->forget([$offset]);
    }
}
