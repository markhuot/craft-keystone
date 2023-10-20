<?php

namespace markhuot\keystone\actions;

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
        // Check if we can be added here
        if ($path) {
            $parentId = last(explode('/', $path));
            if (! empty($parentId)) {
                $parent = Component::find()->where([
                    'id' => $parentId,
                    'elementId' => $elementId,
                    'fieldId' => $fieldId,
                ])->one();
                if (! $parent->getType()->getSlotDefinition($slot)->allows($type)) {
                    throw new \RuntimeException('Not allowed here');
                }
            }
        }

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
