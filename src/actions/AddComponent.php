<?php

namespace markhuot\keystone\actions;

use Illuminate\Support\Collection;
use markhuot\keystone\base\SlotDefinition;
use markhuot\keystone\models\Component;
use markhuot\keystone\models\ComponentData;

use function markhuot\keystone\helpers\base\throw_if;

/**
 * @phpstan-type SlotConfig array{type: string, data: ?array<mixed>, slots: ?array<mixed>}
 */
class AddComponent
{
    /**
     * @param  array<mixed>  $data
     */
    public function handle(
        int $elementId,
        int $fieldId,
        int $sortOrder,
        ?string $path,
        ?string $slotName,
        string $type,
        array $data = [],
    ): Component {
        // Check if we can be added here
        $parent = (new GetParentFromPath)->handle($elementId, $fieldId, $path);
        $slot = $parent?->getType()->getSlotDefinition($slotName);
        throw_if($slot && !$slot->allows($type), 'Not allowed here');

        // Create the data
        $componentData = new ComponentData;
        $componentData->type = $type;
        $componentData->data = $data;
        $componentData->save();

        // Create the component
        $component = new Component;
        $component->elementId = $elementId;
        $component->fieldId = $fieldId;
        $component->dataId = $componentData->id;
        $component->path = $path;
        $component->slot = $slotName;
        $component->type = $type;
        $component->sortOrder = $sortOrder;
        $component->save();

        // Create any default child components
        $this->createDefaultChildrenFor($component);

        // Refresh the instance so we have the correct data reference
        $component->refresh();

        return $component;
    }

    /**
     * @return Collection<array-key, Collection<array-key, Component>>
     */
    protected function createDefaultChildrenFor(Component $component): Collection
    {
        /** @var Collection<array-key, array<SlotConfig>> $slotDefaults */
        $slotDefaults = $component->getType()
            ->getSlotDefinitions()
            ->mapWithKeys(fn (SlotDefinition $slot) => [$slot->getName() => $slot->getDefaults()]);

        return $this->createDefaultsFor($component, $slotDefaults);
    }

    /**
     * @param  Collection<array-key, array<SlotConfig>>  $slotDefaults
     * @return Collection<array-key, Collection<array-key, Component>>
     */
    protected function createDefaultsFor(Component $component, Collection $slotDefaults): Collection
    {
        return $slotDefaults->map(
            fn ($defaults, $slotName) => collect($defaults)->map(
                fn ($config, $index) => $this->createChild($component, $index, $slotName, $config)
            )
        );
    }

    /**
     * @param  array{type: string, data: ?array<mixed>, slots: ?array<mixed>}  $config
     */
    protected function createChild(Component $component, int $index, ?string $slotName, array $config): Component
    {
        $child = (new self)->handle(
            elementId: $component->elementId,
            fieldId: $component->fieldId,
            sortOrder: $index,
            path: $component->getChildPath(),
            slotName: $slotName,
            type: $config['type'],
            data: $config['data'] ?? [],
        );

        /** @var Collection<array-key, array<SlotConfig>> $grandchildren */
        $grandchildren = collect($config['slots'] ?? []);
        $this->createDefaultsFor($child, $grandchildren);

        return $child;
    }
}
