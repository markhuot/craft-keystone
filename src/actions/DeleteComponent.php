<?php

namespace markhuot\keystone\actions;

use markhuot\keystone\models\Component;
use yii\db\Expression;

class DeleteComponent
{
    public function handle(Component $component)
    {
        Component::updateAll(['sortOrder' => new Expression('sortOrder - 1')], ['and',
            ['elementId' => $component->elementId],
            ['fieldId' => $component->fieldId],
            ['path' => $component->path],
            ['>', 'sortOrder', $component->sortOrder],
        ]);

        Component::deleteAll(['and',
            ['elementId' => $component->elementId],
            ['fieldId' => $component->fieldId],
            ['or',
                ['path' => implode('/', array_filter([$component->path, $component->id]))],
                ['like', 'path', implode('/', array_filter([$component->path, $component->id])).'%', false],
            ],
        ]);

        $component->delete();
    }
}
