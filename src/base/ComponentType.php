<?php

namespace markhuot\keystone\base;

use Craft;
use craft\base\FieldInterface;
use craft\helpers\Html;
use Illuminate\Support\Collection;
use markhuot\keystone\models\Component;
use markhuot\keystone\models\ComponentData;
use markhuot\keystone\twig\Exports;
use Twig\Markup;

abstract class ComponentType
{
    protected string $handle;

    protected ?string $name = null;

    // https://phosphoricons.com
    protected string $icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><path d="M64,216a8,8,0,0,1-8-8V165.31a28,28,0,1,1,0-50.62V72a8,8,0,0,1,8-8h46.69a28,28,0,1,1,50.61,0H208a8,8,0,0,1,8,8v42.69a28,28,0,1,0,0,50.62V208a8,8,0,0,1-8,8Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="12"/></svg>';

    /**
     * The category the icon will display under when adding new components
     */
    protected string $category = 'General';

    protected ?array $_exports = null;

    protected array $_accessedSlots = [];

    protected ?array $_schema = null;

    public function __construct(
        protected ?Component $context = null
    ) {
    }

    public function getName(): string
    {
        if ($this->name !== null) {
            return $this->name;
        }

        if ($name = $this->getExport('name')) {
            return $name;
        }

        $parts = explode('/', $this->handle);

        return ucfirst(last($parts));
    }

    public function getCategory(): string
    {
        return $this->getExport('category', $this->category);
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function getIcon(array $attributes = []): Markup|string
    {
        $icon = $this->getExport('icon', $this->icon);

        return new Markup(Html::modifyTagAttributes($icon, $attributes), 'utf-8');
    }

    public function render(array $variables = []): string
    {
        [$mode, $path] = explode(':', $this->getTemplatePath());

        return Craft::$app->getView()->renderTemplate(
            template: $path,
            variables: $variables,
            templateMode: $mode,
        );
    }

    abstract public function getTemplatePath(): string;

    public function getSlotDefinitions()
    {
        return $this->getSchema()[1];
    }

    public function getSlotDefinition(?string $slot)
    {
        return $this->getSlotDefinitions()
            ->where(fn ($defn) => $defn->getName() === $slot)
            ->first();
    }

    /**
     * @return Collection<FieldDefinition>
     */
    public function getFieldDefinitions(): Collection
    {
        return $this->getSchema()[0];
    }

    /**
     * @return Collection<FieldInterface>
     */
    public function getFields(): Collection
    {
        return $this->getFieldDefinitions()
            ->map(fn (FieldDefinition $defn) => $defn->build());
    }

    public function getField(string $handle): ?FieldInterface
    {
        return $this->getFields()->first(fn (FieldInterface $field) => $field->handle === $handle);
    }

    public function getExports($dumb = false): array
    {
        if ($this->_exports) {
            return $this->_exports;
        }

        $componentData = $this->context?->getProps() ?? new ComponentData;
        $componentData->type = $this->getHandle();
        $component = $this->context ?? new Component;
        $component->populateRelation('data', $componentData);
        $attributes = $component->getAttributeBag() ?? new AttributeBag;

        $this->render([
            'component' => $component,
            'props' => $props = ($dumb ? new ComponentData : $componentData),
            'attributes' => $attributes,
            'exports' => $exports = new Exports,
        ]);

        $exports = ['exports' => $exports->exports, 'props' => $props];

        if ($dumb) {
            return $exports;
        }

        return $this->_exports = $exports;
    }

    public function getExport(string $name, mixed $default = null)
    {
        return $this->getExports()['exports'][$name] ?? $default;
    }

    public function defineSlot(string $slotName = null): SlotDefinition
    {
        return $this->_accessedSlots[$slotName] ??= new SlotDefinition($this->context, $slotName);
    }

    protected function getSchema(): array
    {
        if ($this->_schema !== null) {
            return $this->_schema;
        }

        ['exports' => $exports, 'props' => $props] = $this->getExports(true);

        $slotDefinitions = collect($this->_accessedSlots);

        $exportedFieldDefinitions = collect($exports['propTypes'] ?? [])
            ->map(fn (FieldDefinition $defn, string $key) => $defn->handle($key));

        $fieldDefinitions = $props->getAccessed()
            ->merge($exportedFieldDefinitions);

        return $this->_schema = [$fieldDefinitions, $slotDefinitions];
    }
}
