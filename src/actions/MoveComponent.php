<?php

namespace markhuot\keystone\actions;

use markhuot\keystone\enums\MoveComponentPosition;
use markhuot\keystone\models\Component;
use yii\db\Expression;

class MoveComponent
{
    public function handle(Component $source, MoveComponentPosition $position, Component $target, string $slotName = null)
    {
        if ($position === MoveComponentPosition::BEFORE || $position === MoveComponentPosition::AFTER) {
            $this->handleAboveOrBelow($source, $target, $position);
        }

        if ($position === MoveComponentPosition::BEFOREEND) {
            $this->handleBeforeEnd($source, $target, $slotName);
        }
    }

    public function handleAboveOrBelow(Component $source, Component $target, MoveComponentPosition $position)
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
            ['slot' => $source->slot],
            ['path' => $source->path],
            ['>', 'sortOrder', $source->sortOrder],
        ]);

        // Refresh our target to get the updated/correct sortOrder
        $target->refresh();

        // make room for the insertion
        if ($position === MoveComponentPosition::BEFORE) {
            Component::updateAll([
                'sortOrder' => new Expression('sortOrder + 1'),
            ], ['and',
                ['=', 'elementId', $target->elementId],
                ['=', 'fieldId', $target->fieldId],
                ['slot' => $target->slot],
                ['path' => $target->path],
                ['>=', 'sortOrder', $target->sortOrder],
            ]);
        }
        if ($position === MoveComponentPosition::AFTER) {
            Component::updateAll([
                'sortOrder' => new Expression('sortOrder + 1'),
            ], ['and',
                ['=', 'elementId', $target->elementId],
                ['=', 'fieldId', $target->fieldId],
                ['slot' => $target->slot],
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
        $source->slot = $target->slot;
        $source->sortOrder = $position == MoveComponentPosition::BEFORE ? $target->sortOrder - 1 : $target->sortOrder + 1;
        $source->save();

        // move any children of the source
        $newChildPath = implode('/', array_filter([$target->path, $source->id]));
        Component::updateAll([
            'path' => new Expression('REPLACE(path, \''.$originalChildPath.'\', \''.$newChildPath.'\')'),
            'level' => new Expression('level + '.$diff),
        ], ['and',
            ['=', 'elementId', $target->elementId],
            ['=', 'fieldId', $target->fieldId],
            ['like', 'path', $originalChildPath.'%', false],
        ]);

    }

    public function handleBeforeEnd(Component $source, Component $target, string $slotName = null)
    {
        $lastChild = $target->getSlot($slotName)->last();
        if ($lastChild?->getQueryCondition() === $source->getQueryCondition()) {
            return;
        }

        // remove ourselves from the list
        Component::updateAll([
            'sortOrder' => new Expression('sortOrder - 1'),
        ], ['and',
            ['=', 'elementId', $source->elementId],
            ['=', 'fieldId', $source->fieldId],
            ['path' => $source->path],
            ['slot' => $source->slot],
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
        $source->slot = $slotName;
        $source->sortOrder = ($lastChild?->sortOrder ?? -1) + 1;
        $source->save();

        // move any children of the source
        $newPath = implode('/', array_filter([$target->path, $target->id, $source->id]));
        Component::updateAll([
            'path' => $originalPath ? new Expression('REPLACE(path, \''.$originalPath.'\', \''.$newPath.'\')') : new Expression('CONCAT(\''.$newPath.'/\', path)'),
            'level' => new Expression('level + '.$diff),
        ], ['and',
            ['=', 'elementId', $target->elementId],
            ['=', 'fieldId', $target->fieldId],
            ['like', 'path', $originalPath.'%', false],
            ['!=', 'id', $source->id],
        ]);
    }
}
