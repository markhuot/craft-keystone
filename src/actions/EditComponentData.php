<?php

namespace markhuot\keystone\actions;

use markhuot\keystone\models\Component;
use PHPUnit\Framework\Constraint\IsEqualCanonicalizing;

class EditComponentData
{
    public function handle(Component $component, array $data)
    {
        // We're using PHPUnit's array comparator to see if the data has actually changed.
        // This allows us to detect if the arrays are structurally similar even if the order
        // of the keys has changed.
        if ((new IsEqualCanonicalizing($component->data->data))->evaluate($data, '', true)) {
            return $component;
        }

        $component
            ->maybeDuplicateData()
            ->merge($data)
            ->save();

        return $component;
    }
}
