<?php

namespace markhuot\keystone\base;

abstract class Style
{
    abstract public function getInputHtml(): string;

    abstract public function toAttributeArray(): array;

    public function serialize(mixed $value): mixed
    {
        return $value;
    }
}
