<?php

namespace markhuot\keystone\factories;

use craft\elements\Entry;
use markhuot\craftpest\factories\Factory;
use markhuot\keystone\models\ComponentData;

class Component extends Factory
{
    public function newElement()
    {
        return new \markhuot\keystone\models\Component;
    }

    public function definition(int $index = 0)
    {
        $data = new ComponentData();
        $data->type = 'keystone/text';
        $data->save();

        return [
            'elementId' => 1,
            'fieldId' => 1,
            'dataId' => $data->id,
            'sortOrder' => 0,
            'path' => null,
        ];
    }

    public function store($element)
    {
        return $element->save();
    }
}
