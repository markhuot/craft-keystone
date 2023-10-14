<?php

namespace markhuot\keystone\actions;

use craft\helpers\Db;
use craft\helpers\StringHelper;
use markhuot\keystone\db\Table;
use markhuot\keystone\models\Component;
use markhuot\keystone\models\ComponentData;

class AddComponent
{
    public function handle(
        int $elementId,
        int $fieldId,
        int $sortOrder,
        ?string $path,
        ?string $slot,
        string $type
    ): Component {
        $componentData = new ComponentData;
        $componentData->type = $type;
        $componentData->save();

        $component = new Component;
        $component->elementId = $elementId;
        $component->fieldId = $fieldId;
        $component->dataId = $componentData->id;
        $component->path = $path;
        $component->slot = $slot;
        $component->type = $type;
        $component->sortOrder = $sortOrder;
        $component->save();

        $component->refresh();

        return $component;
    }
}
