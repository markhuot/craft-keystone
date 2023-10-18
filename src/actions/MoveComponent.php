<?php

namespace markhuot\keystone\actions;

use markhuot\keystone\models\Component;
use yii\db\Expression;

class MoveComponent
{
    public function handle(Component $source, Component $target, string $position)
    {
        if ($position === 'above' || $position === 'below') {
            $this->handleAboveOrBelow($source, $target, $position);
        }

        if ($position === 'beforeend') {
            $this->handleBeforeEnd($source, $target, $position);
        }
    }

    public function handleAboveOrBelow(Component $source, Component $target, string $position)
    {
        // get the change in depth/level
        $originalChildPath = implode('/', array_filter([$source->path, $source->id]));
        $diff = $target->level - $source->level;

        // remove ourselves from the list
        Component::updateAll([
            'sortOrder' => new Expression('sortOrder - 1'),
        ], ['and',
            ['=', 'elementId', $source->elementId],
            ['=', 'fieldId', $source->fieldId],
            ['path' => $source->path],
            ['>', 'sortOrder', $source->sortOrder],
        ]);

        // Refresh our target to get the updated/correct sortOrder
        $target->refresh();

        // make room for the insertion
        if ($position === 'above') {
            Component::updateAll([
                'sortOrder' => new Expression('sortOrder + 1'),
            ], ['and',
                ['=', 'elementId', $target->elementId],
                ['=', 'fieldId', $target->fieldId],
                ['path' => $target->path],
                ['>=', 'sortOrder', $target->sortOrder],
            ]);
        }
        if ($position === 'below') {
            Component::updateAll([
                'sortOrder' => new Expression('sortOrder + 1'),
            ], ['and',
                ['=', 'elementId', $target->elementId],
                ['=', 'fieldId', $target->fieldId],
                ['path' => $target->path],
                ['>', 'sortOrder', $target->sortOrder],
            ]);
        }
        
        // Refresh the target again, in case it changed, so we're setting the correct
        // sort order
        $target->refresh();
        $source->refresh();
        
        // move the source to the target
        $source->path = $target->path;
        $source->sortOrder = $position == 'above' ? $target->sortOrder - 1 : $target->sortOrder + 1;
        $source->save();

        // move any children of the source
        $newChildPath = implode('/', array_filter([$target->path, $source->id]));
        Component::updateAll([
            'path' => new Expression('REPLACE(path, \'' . $originalChildPath . '\', \'' . $newChildPath . '\')'),
            'level' => new Expression('level + ' . $diff),
        ], ['and',
            ['=', 'elementId', $target->elementId],
            ['=', 'fieldId', $target->fieldId],
            ['like', 'path', $originalChildPath.'%', false],
        ]);

    }

    public function handleBeforeEnd(Component $source, Component $target, string $position)
    {
        // remove ourselves from the list
        Component::updateAll([
            'sortOrder' => new Expression('sortOrder - 1'),
        ], ['and',
            ['=', 'elementId', $source->elementId],
            ['=', 'fieldId', $source->fieldId],
            ['path' => $source->path],
            ['>', 'sortOrder', $source->sortOrder],
        ]);

        // Refresh the target again, in case it changed, so we're setting the correct
        // sort order
        $target->refresh();
        $source->refresh();

        // get the change in depth/level
        $originalPath = implode('/', array_filter([$source->path, $source->id]));
        $diff = $target->level + 1 - $source->level;

        // move the source
        $source->path = implode('/', array_filter([$target->path, $target->id]));
        $source->sortOrder = ($target->getSlot()->last()?->sortOrder ?? -1) + 1;
        $source->save();

        // move any children of the source
        $newPath = implode('/', array_filter([$target->path, $target->id, $source->id]));
        Component::updateAll([
            'path' => $originalPath ? new Expression('REPLACE(path, \'' . $originalPath . '\', \'' . $newPath . '\')') : new Expression('CONCAT(\'' . $newPath . '/\', path)'),
            'level' => new Expression('level + ' . $diff),
        ], ['and',
            ['=', 'elementId', $target->elementId],
            ['=', 'fieldId', $target->fieldId],
            ['like', 'path', $originalPath.'%', false],
            ['!=', 'id', $source->id],
        ]);
    }
}
