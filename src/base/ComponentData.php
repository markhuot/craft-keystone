<?php

namespace markhuot\keystone\base;

use ArrayAccess;

class ComponentData implements ArrayAccess
{
    public function __construct(
        protected ComponentType $type,
        protected array $data = [],
        protected array $accessed = [],
    ) { }

//    public function __isset(string $key)
//    {
//        return true;
//    }
//
//    public function __get(string $key)
//    {
//        $this->accessed[$key] = $this->accessed[$key] ?? new FieldDefinition($key);
//
//        return $this->data[$key] ?? null;
//    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function getAccessed(): array
    {
        return $this->accessed;
    }

    public function offsetExists(mixed $offset): bool
    {
        return true;
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (is_string($offset)) {
            $this->accessed[$offset] = $this->accessed[$offset] ?? new FieldDefinition($offset);
        }

        $value = $this->data[$offset] ?? null;

        if ($value) {
            if (isset($this->type->getFields()[$offset])) {
                $value = $this->type->getFields()[$offset]->normalizeValue($value);
            }
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
        return $this->accessed[$handle] = $this->accessed[$handle] ?? new FieldDefinition($handle);
    }
}
