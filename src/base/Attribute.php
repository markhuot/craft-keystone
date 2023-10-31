<?php

namespace markhuot\keystone\base;

use Illuminate\Support\Collection;

abstract class Attribute
{
    abstract public function getInputHtml(): string;

    public function toAttributeArray(): array
    {
        $classNames = $this->getCssRules()
            ->map(\Craft::$app->getView()->registerCssDeclaration(...));

        return ['class' => $classNames->join(' ')];
    }

    public function getCssRules(): Collection
    {
        return collect();
    }

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
