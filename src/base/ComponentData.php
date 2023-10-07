<?php

namespace markhuot\keystone\base;

use ArrayAccess;
use Closure;
use Illuminate\Support\Collection;

class ComponentData implements ArrayAccess
{
    protected array $accessed = [];

    public function __construct(
        protected array $data = [],
        protected ?Closure $normalize=null,
    ) { }

    public function toArray(): array
    {
        return $this->data;
    }

    public function getAccessed(): Collection
    {
        return collect($this->accessed);
    }

    public function offsetExists(mixed $offset): bool
    {
        return true;
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (is_string($offset)) {
            $this->accessed[$offset] = $this->accessed[$offset] ?? new FieldDefinition();
        }

        $value = $this->data[$offset] ?? null;

        if ($this->normalize) {
            return ($this->normalize)($offset, $value);
        }

        return $value;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    public function defineField(string $handle): FieldDefinition
    {
        return $this->accessed[$handle] = $this->accessed[$handle] ?? new FieldDefinition();
    }
}
