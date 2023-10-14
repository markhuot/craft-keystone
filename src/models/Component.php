<?php

namespace markhuot\keystone\models;

use Craft;
use craft\base\Element;
use markhuot\keystone\actions\GetComponentType;
use markhuot\keystone\base\AttributeBag;
use markhuot\keystone\base\ComponentType;
use markhuot\keystone\collections\SlotCollection;
use markhuot\keystone\db\ActiveRecord;
use markhuot\keystone\db\Table;
use Twig\Markup;

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

    public function __get($name)
    {
        $value = parent::__get($name);

        if ($name === 'data' && $value === null) {
            $this->populateRelation($name, $data=new ComponentData);
            $value = $data;
        }

        if ($name === 'data') {
            $value->setNormalizer(fn ($handle, $value) => $this->getType()->getField($handle)?->normalizeValue($value) ?? $value);
        }

        return $value;
    }

    public function setType($type)
    {
        $this->data->setAttribute('type', $type);
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

    public function getAccessed()
    {
        return $this->accessed;
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

    public function render()
    {
        return new Markup($this->getType()->render([
            'component' => $this,
            'props' => $this->data,
            'attributes' => new AttributeBag($this->data['_styles']),
        ]), 'utf-8');
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

    public function maybeDuplicateData(): ComponentData
    {
        // See how many components reference this data
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
