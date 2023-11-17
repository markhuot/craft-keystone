<?php

namespace markhuot\keystone\base;

use markhuot\keystone\validators\Required;
use markhuot\keystone\validators\Safe;

class Model extends \craft\base\Model
{
    public function safeAttributes()
    {
        $reflect = new \ReflectionClass($this);
        $properties = collect($reflect->getProperties(\ReflectionProperty::IS_PUBLIC))
            ->filter(fn (\ReflectionProperty $property) => $property->getAttributes(Safe::class))
            ->map(fn (\ReflectionProperty $property) => $property->getName())
            ->all();

        return array_merge(parent::safeAttributes(), $properties);
    }

    public function rules(): array
    {
        $reflect = new \ReflectionClass($this);
        $required = collect($reflect->getProperties(\ReflectionProperty::IS_PUBLIC))
            ->filter(fn (\ReflectionProperty $property) => $property->getAttributes(Required::class))
            ->map(fn (\ReflectionProperty $property) => $property->getName())
            ->all();

        return [
            [$required, 'required'],
        ];
    }
}
