<?php

namespace markhuot\keystone\factories;

use markhuot\craftpest\factories\Factory;

class Component extends Factory
{
    public function newElement()
    {
        return new \markhuot\keystone\models\Component;
    }

    public function definition(int $index = 0)
    {
        return [
            'elementId' => 1,
            'fieldId' => 1,
            'type' => 'keystone/text',
            'sortOrder' => 0,
        ];
    }

    public function store($element)
    {
        return $element->save();
    }
}
