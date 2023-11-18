<?php

namespace markhuot\keystone\models;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\db\ActiveQuery;
use Illuminate\Support\Collection;
use markhuot\keystone\actions\GetComponentType;
use markhuot\keystone\actions\NormalizeFieldDataForComponent;
use markhuot\keystone\base\AttributeBag;
use markhuot\keystone\base\ComponentType;
use markhuot\keystone\base\ContextBag;
use markhuot\keystone\collections\SlotCollection;
use markhuot\keystone\db\ActiveRecord;
use markhuot\keystone\db\Table;
use markhuot\keystone\events\AfterPopulateTree;
use yii\base\Event;
use yii\db\conditions\OrCondition;

use function markhuot\keystone\helpers\base\app;
use function markhuot\keystone\helpers\base\throw_if;

/**
 * @property int $id
 * @property int $elementId
 * @property int $fieldId
 * @property int $dataId
 * @property ?string $path
 * @property ?string $slot
 * @property int $level
 * @property ComponentData $data
 */
class Component extends ActiveRecord
{
    const AFTER_POPULATE_TREE = 'afterPopulateTree';

    /** @var array<Component> */
    protected ?Collection $slotted = null;

    protected array $context = [];

    protected ?Component $renderParent = null;

    protected ?ComponentType $_type = null;

    public static function factory(): \markhuot\keystone\factories\Component
    {
        return new \markhuot\keystone\factories\Component;
    }

    public function getElement(): ElementInterface
    {
        $element = app()->getElements()->getElementById($this->elementId);
        throw_if(! $element, 'An element with the id '.$this->elementId.' could not be found');

        return $element;
    }

    public function getField(): FieldInterface
    {
        $field = app()->getFields()->getFieldById($this->fieldId);
        throw_if(! $field, 'A field with the id '.$this->fieldId.' could not be found');

        return $field;
    }

    public function getData(): ActiveQuery
    {
        return $this->hasOne(ComponentData::class, ['id' => 'dataId']);
    }

    /**
     * @return array<string>
     */
    public static function primaryKey(): array
    {
        return ['id', 'elementId', 'fieldId'];
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getQueryCondition(): array
    {
        return collect(static::primaryKey())->mapWithKeys(fn ($key) => [$key => $this->getAttribute($key)])->toArray();
    }

    public function setType(string $type): self
    {
        $this->data->type = $type;

        return $this;
    }

    public function getType(): ComponentType
    {
        return $this->_type ??= (new GetComponentType($this))->byType($this->data->type);
    }

    public function setComponentType(ComponentType $type): self
    {
        $this->_type = $type;

        return $this;
    }

    public function refresh()
    {
        $this->slotted = null;

        return parent::refresh();
    }

    public function __get($name)
    {
        $value = parent::__get($name);

        if ($name === 'data' && $value === null) {
            $this->populateRelation($name, $data = new ComponentData);
            $value = $data;
        }

        if ($name === 'data' && $value instanceof ComponentData) {
            $value->setNormalizer((new NormalizeFieldDataForComponent($this))->handle(...));
        }

        return $value;
    }

    public static function tableName()
    {
        return Table::COMPONENTS;
    }

    /**
     * @param  array<Component>  $components
     */
    public function setSlotted(Collection $components): self
    {
        $this->slotted = $components;

        return $this;
    }

    public function afterPopulateTree(Collection $components)
    {
        $event = new AfterPopulateTree;
        $event->components = $components;

        Event::trigger(self::class, self::AFTER_POPULATE_TREE, $event);
    }

    /**
     * @return Collection<Component>|null
     */
    public function getSlotted(): ?Collection
    {
        return $this->slotted;
    }

    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function mergeContext(array $context): self
    {
        $this->context = [
            ...$this->context,
            ...$context,
        ];

        return $this;
    }

    public function getContext(): ContextBag
    {
        return new ContextBag($this->context);
    }

    public function setRenderParent(Component $parent): self
    {
        $this->renderParent = $parent;

        return $this;
    }

    public function safeAttributes()
    {
        return array_merge(parent::safeAttributes(), ['path', 'slot']);
    }

    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            [['elementId', 'fieldId', 'dataId', 'sortOrder'], 'required'],
        ];
    }

    public function setPath(string|array|null $path): void
    {
        if (is_array($path)) {
            $path = implode('/', array_filter($path));
        }

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

    public function render(array $props = []): string
    {
        return $this->getType()->render([
            'component' => $this,
            'props' => $this->getProps(),
            'attributes' => $this->getAttributeBag(),
            ...$props,
        ]);
    }

    public function getProps(): ComponentData
    {
        return $this->data;
    }

    public function getProp(string $key, mixed $default = null)
    {
        return $this->getProps()->get($key) ?? $default;
    }

    public function getAttributeBag(): AttributeBag
    {
        return new AttributeBag($this->data->getDataAttributes());
    }

    public function getSummary(): ?string
    {
        return $this->getType()->getExport('summary');
    }

    public function __toString(): string
    {
        $html = $this->render();

        return $html;
    }

    public function isDiscendantOf(Component $component, string $slotName = null): bool
    {
        return str_starts_with($this->path ?? '', $component->getChildPath() ?? '');
    }

    public function isDirectDiscendantOf(Component $component, string $slotName = null): bool
    {
        return $component->getChildPath() === $this->path && $slotName === $this->slot;
    }

    public function isParentOf(Component $component, string $slotName = null): bool
    {
        return $this->getChildPath() === $component->path && $slotName === $component->slot;
    }

    public function getSlot(string $name = null): SlotCollection
    {
        $this->getType()->defineSlot($name);

        if ($this->slotted === null && $this->elementId && $this->fieldId) {
            $components = Component::find()
                ->with('data')
                ->where(['and',
                    ['elementId' => $this->elementId],
                    ['fieldId' => $this->fieldId],

                    // this is intentionally left out. We don't want to limit our query by slot name
                    // because children of this component may not share the same name. We need to pull
                    // all children out of the database and then the slot name filtering happens below
                    // before being returned.
                    // ['slot' => $name],
                    new OrCondition(array_filter([
                        ! $this->getChildPath() ? ['path' => null] : null,
                        ['like', 'path', $this->getChildPath().'%', false],
                    ])),
                ])
                ->orderBy('sortOrder')
                ->collect();

            $this->afterPopulateTree($components);
            $this->setSlotted($components);
        }

        $components = ($this->slotted ?? collect())
            ->where(fn (Component $component) => $component->isDirectDiscendantOf($this, $name))
            ->each(function (Component $component) {
                $components = collect($this->slotted)
                    ->where(fn (Component $c) => $c->isDiscendantOf($component));

                $component->setSlotted($components)

                    // As we delve through the render tree pass some state around so we know
                    // where each child is rendering and can act accordingly. For example,
                    //
                    // 1. we set pass the context down so if a section sets a context of "bg: blue"
                    //    then any child components will also see that same context.
                    // 2. set the render parent so child components know who is initiating
                    //    the rendering. This allows us to affect children based on their
                    //    parent tree.
                    ->mergeContext($this->context)
                    ->setRenderParent($this);
            })

            // re-key components so they are indexed sequentially since the ->where
            // call above may have removed some of the keys.
            ->values();

        return new SlotCollection($components->all(), $this, $name);
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
        $this->level = count(array_filter(explode('/', $this->path ?? '')));
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
