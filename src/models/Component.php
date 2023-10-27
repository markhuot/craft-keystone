<?php

namespace markhuot\keystone\models;

use Craft;
use markhuot\keystone\actions\GetComponentType;
use markhuot\keystone\actions\NormalizeFieldDataForComponent;
use markhuot\keystone\base\AttributeBag;
use markhuot\keystone\base\ComponentType;
use markhuot\keystone\base\SlotDefinition;
use markhuot\keystone\collections\SlotCollection;
use markhuot\keystone\db\ActiveRecord;
use markhuot\keystone\db\Table;

/**
 * @property int $id
 * @property int $elementId
 * @property int $fieldId
 * @property int $dataId
 */
class Component extends ActiveRecord
{
    protected array $accessed = [];

    protected ?array $slotted = null;

    public static function factory()
    {
        return new \markhuot\keystone\factories\Component;
    }

    public function getElement()
    {
        return Craft::$app->getElements()->getElementById($this->elementId);
    }

    public function getField()
    {
        return Craft::$app->getFields()->getFieldById($this->fieldId);
    }

    public function getData()
    {
        return $this->hasOne(ComponentData::class, ['id' => 'dataId']);
    }

    public static function primaryKey()
    {
        return ['id', 'elementId', 'fieldId'];
    }

    public function getQueryCondition(): array
    {
        return collect(static::primaryKey())->mapWithKeys(fn ($key) => [$key => $this->getAttribute($key)])->toArray();
    }

    public function setType(string $type): self
    {
        $this->data->type = $type;

        return $this;
    }

    public function __get($name)
    {
        $value = parent::__get($name);

        if ($name === 'data' && $value === null) {
            $this->populateRelation($name, $data = new ComponentData);
            $value = $data;
        }

        if ($name === 'data') {
            $value->setNormalizer((new NormalizeFieldDataForComponent($this))->handle(...));
        }

        return $value;
    }

    public function getType(): ComponentType
    {
        return (new GetComponentType)->byType($this->data->type);
    }

    public static function tableName()
    {
        return Table::COMPONENTS;
    }

    public function setSlotted(array $components)
    {
        $this->slotted = $components;
    }

    public function getSlotted()
    {
        return $this->slotted;
    }

    public function getAccessed()
    {
        return collect($this->accessed);
    }

    public function safeAttributes()
    {
        return array_merge(parent::safeAttributes(), ['path', 'slot']);
    }

    public function rules(): array
    {
        return [
            [['elementId', 'fieldId', 'dataId', 'sortOrder'], 'required'],
        ];
    }

    public function setPath(?string $path): void
    {
        if (is_string($path)) {
            $path = trim($path, '/');
        }

        if (empty($path)) {
            $path = null;
        }

        $this->setAttribute('path', $path);
    }

    public function setSlot(?string $slot): void
    {
        $this->setAttribute('slot', $slot === '' ? null : $slot);
    }

    public function render(): string
    {
        return $this->getType()->render([
            'component' => $this,
            'props' => $this->data,
            'attributes' => new AttributeBag($this->data['_attributes']),
        ]);
    }

    public function getExports(): array
    {
        $exports = new class
        {
            public array $exports = [];

            public function add($key, $value)
            {
                $this->exports[$key] = $value;
            }
        };

        $this->getType()->render([
            'component' => $this,
            'props' => $this->data,
            'attributes' => new AttributeBag($this->data['_attributes']),
            'exports' => $exports,
        ]);

        return $exports->exports;
    }

    public function __toString(): string
    {
        $html = $this->render();

        return $html;
    }

    public function isDirectDiscendantOf(Component $component, string $slotName = null): bool
    {
        return $component->getChildPath() === $this->path && $slotName === $this->slot;
    }

    public function isParentOf(Component $component, string $slotName = null): bool
    {
        return $this->getChildPath() === $component->path && $slotName === $component->slot;
    }

    public function defineSlot(string $slotName = null)
    {
        return $this->accessed[$slotName] ??= new SlotDefinition($this, $slotName);
    }

    public function getSlot($name = null): SlotCollection
    {
        $this->accessed[$name] ??= new SlotDefinition($this, $name);

        $path = ltrim(($this->path ?? '').'/'.$this->id, '/');
        if (empty($path)) {
            $path = null;
        }

        if ($this->slotted !== null) {
            $components = collect($this->slotted)
                ->where(fn (Component $component) => $component->isDirectDiscendantOf($this, $name))
                ->each(function (Component $component) {
                    $components = collect($this->slotted ?? [])
                        ->where(function (Component $c) use ($component) {
                            return str_starts_with($c->path, $component->getChildPath());
                        })
                        ->toArray();

                    $component->setSlotted($components);
                })
                ->toArray();
        } elseif ($this->elementId && $this->fieldId) {
            $components = Component::find()->where([
                'elementId' => $this->elementId,
                'fieldId' => $this->fieldId,
                'path' => $path,
                'slot' => $name,
            ])->orderBy('sortOrder')->all();
        } else {
            $components = [];
        }

        return new SlotCollection($components, $this, $name);
    }

    public function getChildPath(): ?string
    {
        $path = implode('/', array_filter([$this->path, $this->id]));

        return ($path !== '') ? $path : null;
    }

    protected function prepareForDb(): void
    {
        parent::prepareForDb();

        $max = Component::find()->max('id') ?? 0;
        $this->id = $this->id ?? ($max + 1);
        $this->level = count(array_filter(explode('/', $this->path)));
    }

    /**
     * Duplicate the data if it is shared by multiple components so that we can
     * make changes to the data without affecting other instances using the same
     * reference.
     */
    public function maybeDuplicateData(): ComponentData
    {
        // See how many components reference this data. We really only case if it's
        // one or more than one so we can limit 2 to check the count with better performance
        $count = Component::find()->where(['dataId' => $this->dataId])->limit(2)->count();

        // If we're the only component referencing this data we can just edit it place
        if ($count === 1) {
            return $this->data;
        }

        // Otherwise we need to duplicate the data and point ourselves at the new data
        $this->dataId = $this->data->duplicate()->id;
        $this->save();
        $this->refresh();

        return $this->data;
    }
}
