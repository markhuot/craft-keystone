<?php

namespace markhuot\keystone\actions;

use markhuot\keystone\models\Component;

class GetParentFromPath
{
    public function handle(int $elementId, int $fieldId, ?string $path): Component
    {
        $parentId = last(explode('/', $path));

        return $parentId ? Component::findOne([
            'elementId' => $elementId,
            'fieldId' => $fieldId,
            'id' => $parentId,
        ]) : (new Component)->setType('keystone/fragment');
    }
}
