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
        $parent = (new GetParentFromPath)->handle($elementId, $fieldId, $path);
        if ($parent && ! $parent->getType()->getSlotDefinition($slot)->allows($type)) {
            throw new \RuntimeException('Not allowed here');
        }

        // Create the data
        $componentData = new ComponentData;
        $componentData->type = $type;
        $componentData->save();

        // Create the component
        $component = new Component;
        $component->elementId = $elementId;
        $component->fieldId = $fieldId;
        $component->dataId = $componentData->id;
        $component->path = $path;
        $component->slot = $slot;
        $component->type = $type;
        $component->sortOrder = $sortOrder;
        $component->save();

        // Refresh the instance so we have the correct data reference
        $component->refresh();

        return $component;
    }
}
