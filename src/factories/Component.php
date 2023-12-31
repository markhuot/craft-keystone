<?php

namespace markhuot\keystone\factories;

use craft\base\FieldInterface;
use markhuot\craftpest\factories\Entry;
use markhuot\craftpest\factories\Factory;
use markhuot\keystone\fields\Keystone;
use markhuot\keystone\models\ComponentData;
use SplObjectStorage;

/**
 * @method self type(string $type)
 * @method \markhuot\keystone\models\Component create(array $attributes = [])
 */
class Component extends Factory
{
    public static $tests;

    public function newElement()
    {
        return new \markhuot\keystone\models\Component;
    }

    public function definition(int $index = 0)
    {
        // $data = new ComponentData();
        // $data->type = 'keystone/text';
        // $data->save();

        $field = collect(\Craft::$app->getFields()->getAllFields())
            ->first(fn (FieldInterface $field) => get_class($field) === Keystone::class);

        if (function_exists('test')) {
            static::$tests ??= new SplObjectStorage;
            $entry = (static::$tests[test()->target] ??= Entry::factory()->create());
        }

        return [
            'elementId' => $entry->id,
            'fieldId' => $field->id,
            // 'dataId' => $data->id,
            'sortOrder' => 0,
            'path' => null,
        ];
    }

    public function store($element)
    {
        if (is_null($element->data->type)) {
            $element->setType('keystone/text');
        }

        if (is_null($element->getAttribute('dataId'))) {
            $element->data->save();
            $element->dataId = $element->data->id;
        }

        return $element->save();
    }
}
