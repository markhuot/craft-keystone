<?php

namespace markhuot\keystone\models;

use markhuot\keystone\actions\GetComponentType;
use markhuot\keystone\base\ComponentData;
use markhuot\keystone\base\ComponentType;
use markhuot\keystone\collections\SlotCollection;
use markhuot\keystone\db\ActiveRecord;
use markhuot\keystone\db\Table;
use Twig\Markup;

class Component extends ActiveRecord
{
    protected ComponentData $_data;
    protected ?array $slotted = null;
    protected array $accessed = [];

    public static function factory()
    {
        return new \markhuot\keystone\factories\Component;
    }

    public function safeAttributes()
    {
        return array_merge(parent::safeAttributes(), ['path', 'data']);
    }

    public function rules(): array
    {
        return [
            [['elementId', 'fieldId', 'sortOrder', 'slot', 'type'], 'required'],
        ];
    }

    public static function tableName()
    {
        return Table::COMPONENTS;
    }

    public function setPath(string $path): ?string
    {
        $path = trim($path, '/');

        if (empty($path)) {
            $path = null;
        }

        return $path;
    }

    public function getChildPath(): ?string
    {
        $path = implode('/', array_filter([$this->path, $this->id]));

        return ($path !== '') ? $path : null;
    }

    protected function prepareForDb(): void
    {
        parent::prepareForDb();

        $this->level = count(array_filter(explode('/', $this->path)));
    }

    public function getType(): ComponentType
    {
        return (new GetComponentType)->byType($this->type);
    }

    public function render()
    {
        return new Markup($this->getType()->render([
            'component' => $this,
            'props' => $this->data,
        ]), 'utf-8');
    }

    public function setSlotted(array $components)
    {
        $this->slotted = $components;
    }

    public function getAccessed()
    {
        return $this->accessed;
    }

    public function getSlot($name=null)
    {
        $this->accessed[] = $name;

        $path = ltrim(($this->path ?? '') . '/' . $this->id, '/');
        if (empty($path)) {
            $path = null;
        }

        if ($this->slotted !== null) {
            $components = collect($this->slotted)
                ->where(fn (Component $component) => $this->getChildPath() === $component->path)
                ->each(function (Component $component) {
                    $components = collect($this->slotted ?? [])
                        ->where(function (Component $c) use ($component) {
                            return str_starts_with($c->path, $component->getChildPath());
                        })
                        ->toArray();

                    $component->setSlotted($components);
                })
                ->toArray();
        }
        else if ($this->elementId && $this->fieldId) {
            $components = Component::find()->where([
                'elementId' => $this->elementId,
                'fieldId' => $this->fieldId,
                'path' => $path,
                'slot' => $name,
            ])->orderBy('sortOrder')->all();
        }
        else {
            $components = [];
        }
        return new SlotCollection($this, $name, $components);
    }

    public function newData(): ComponentData
    {
        return new ComponentData(
            data: $this->getRawAttributes()['data'] ?? [],
            normalize: fn ($key, $value) => $this->getType()->getField($key)?->normalizeValue($value) ?? $value,
        );
    }

    /**
     * We override the `slot` attribute for a method call to ->getSlot() and then expose
     * the raw ->attributes['slot'] as ->__get('slotName')
     */
    public function __get($name)
    {
        if ($name === 'slot') {
            return $this->getSlot()->toHtml();
        }

        if ($name === 'slotName') {
            return $this->attributes['slot'] ?? null;
        }

        if ($name === 'data') {
            if (isset($this->_data)) {
                return $this->_data;
            }

            return $this->_data = $this->newData();
        }

        return parent::__get($name);
    }
}
