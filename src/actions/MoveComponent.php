<?php

namespace markhuot\keystone\actions;

use markhuot\keystone\models\Component;
use yii\db\Expression;

class MoveComponent
{
    public function handle(Component $source, Component $target, string $position)
    {
        // remove ourselves from the list
        Component::updateAll([
            'sortOrder' => new Expression('sortOrder - 1')
        ], ['and',
            ['=', 'elementId', $source->elementId],
            ['=', 'fieldId', $source->fieldId],
            ['path' => $source->path],
            ['>', 'sortOrder', $source->sortOrder]
        ]);

        // Refresh our target to get the updated/correct sortOrder
        $target->refresh();

        // make room for the insertion
        if ($position === 'above') {
            Component::updateAll([
                'sortOrder' => new Expression('sortOrder + 1')
            ], ['and',
                ['=', 'elementId', $target->elementId],
                ['=', 'fieldId', $target->fieldId],
                ['path' => $target->path],
                ['>=', 'sortOrder', $target->sortOrder]
            ]);
        }
        if ($position === 'below')
        {
            Component::updateAll([
                'sortOrder' => new Expression('sortOrder + 1')
            ], ['and',
                ['=', 'elementId', $target->elementId],
                ['=', 'fieldId', $target->fieldId],
                ['path' => $target->path],
                ['>', 'sortOrder', $target->sortOrder]
            ]);
        }

        // Refresh the target again, in case it changed, so we're setting the correct
        // sort order
        $target->refresh();
        $source->refresh();

        $source->path = $target->path;
        $source->sortOrder = $position == 'above' ? $target->sortOrder - 1 : $target->sortOrder + 1;
        $source->save();
    }
}
