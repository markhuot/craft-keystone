<?php

namespace markhuot\keystone\db;

use ReflectionClass;

class ActiveRecord extends \craft\db\ActiveRecord
{
    public function __set($name, $value): void
    {
        if (method_exists($this, $methodName= 'set' . ucfirst($name))) {
            $value = $this->{$methodName}($value);
        }

        parent::__set($name, $value);
    }

    public function getRawAttributes(): array
    {
        $reflect = new ReflectionClass($this);
        $parent = $reflect->getParentClass()->getParentClass()->getParentClass()->getParentClass();

        return $parent->getProperty('_attributes')->getValue($this);
    }
}
