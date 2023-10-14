<?php

namespace markhuot\keystone\base;

use Craft;
use craft\base\FieldInterface;
use craft\elements\Entry;
use Illuminate\Support\Collection;
use markhuot\keystone\models\Component;
use markhuot\keystone\base\FieldDefinition;
use Twig\Markup;

abstract class ComponentType
{
    protected string $handle;

    // https://phosphoricons.com
    protected string $icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><path d="M64,216a8,8,0,0,1-8-8V165.31a28,28,0,1,1,0-50.62V72a8,8,0,0,1,8-8h46.69a28,28,0,1,1,50.61,0H208a8,8,0,0,1,8,8v42.69a28,28,0,1,0,0,50.62V208a8,8,0,0,1-8,8Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="12"/></svg>';

    public function getName(): string
    {
        $parts = explode('/', $this->handle);

        return ucfirst(last($parts));
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function getIcon(array $attributes = []): Markup|string
    {
        $attributes = collect($attributes)
            ->map(fn ($v, $k) => "{$k}=\"{$v}\"")
            ->join(' ');

        return new Markup(str_replace('<svg', '<svg '.$attributes, $this->icon), 'utf-8');
    }

    public function render(array $variables=[]): string
    {
        [$mode, $path] = explode(':', $this->getTemplatePath());

        return Craft::$app->getView()->renderTemplate(
            template: $path,
            variables: $variables,
            templateMode: $mode,
        );
    }

    abstract public function getTemplatePath(): string;

    /**
     * @return array{fields: Collection<FieldInterface>}
     */
    public function getSchema(): array
    {
        return [
            'fields' => collect($this->getFieldConfig())->mapInto(FieldDefinition::class)->map->build(),
            'slots' => collect($this->getSlotConfig()),
        ];
    }

    public function getField(string $handle): ?FieldInterface
    {
        return $this->getSchema()['fields'][$handle] ?? null;
    }

    public function hasSlots(): bool
    {
        return !!count($this->getSlotConfig());
    }

    protected abstract function getFieldConfig(): array;
    protected abstract function getSlotConfig(): array;
}
