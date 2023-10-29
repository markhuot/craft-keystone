<?php

namespace markhuot\keystone\base;

class ContextBag
{
    public function __construct(
        protected array $context
    ) { }

    public function __isset($key)
    {
        return true;
    }

    public function __get($key)
    {
        return $this->context[$key] ?? null;
    }
}