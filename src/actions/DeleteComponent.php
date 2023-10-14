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
            ['>', 'sortOrder', $component->sortOrder],
        ]);

        $component->delete();
    }
}
