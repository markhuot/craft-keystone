<?php

namespace markhuot\keystone\base;

use Iterator;

class ContextBag implements Iterator
{
    protected int $index = 0;

    public function __construct(
        protected array $context
    ) {
    }

    public function __isset($key)
    {
        return true;
    }

    public function __get($key)
    {
        return $this->context[$key] ?? null;
    }

    public function current(): mixed
    {
        return $this->context[array_keys($this->context)[$this->index]];
    }

    public function key(): mixed
    {
        return array_keys($this->context)[$this->index];
    }

    public function next(): void
    {
        $this->index++;
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function valid(): bool
    {
        return isset(array_keys($this->context)[$this->index]);
    }
}
