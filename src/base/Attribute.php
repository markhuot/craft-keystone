<?php

namespace markhuot\keystone\base;

abstract class Attribute
{
    abstract public function getInputHtml(): string;

    abstract public function toAttributeArray(): array;

    public function serialize(mixed $value): mixed
    {
        return $value;
    }

    public function getName()
    {
        $reflect = new \ReflectionClass($this);

        return ucfirst($reflect->getShortName());
    }
}
