<?php

namespace markhuot\keystone\models;

use craft\web\View;
use markhuot\keystone\actions\GetComponentTypes;
use markhuot\keystone\base\ComponentData;
use markhuot\keystone\base\ComponentType;
use markhuot\keystone\db\ActiveRecord;
use markhuot\keystone\db\Table;
use Twig\Markup;

class Component extends ActiveRecord
{
    protected ComponentData $_data;

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

    protected function prepareForDb(): void
    {
        parent::prepareForDb();

        $this->level = count(array_filter(explode('/', $this->path)));
    }

    public function getType()
    {
        return (new GetComponentTypes)->handle()
            ->first(fn (ComponentType $component) => $component->getType() === $this->type);
    }

    public function render()
    {
        return new Markup($this->getType()->render(['component' => $this]), 'utf-8');
    }

    public function getSlot($name=null)
    {
        $path = ltrim(($this->path ?? '') . '/' . $this->id, '/');
        if (empty($path)) {
            $path = null;
        }

        return new Markup(\Craft::$app->getView()->renderTemplate('keystone/_slot', [
            'component' => $this,
            'components' => Component::find()->where([
                'elementId' => $this->elementId,
                'fieldId' => $this->fieldId,
                'path' => $path,
                'slot' => $name,
            ])->orderBy('sortOrder')->all(),
        ], View::TEMPLATE_MODE_CP), 'utf-8');
    }

    public function newData(): ComponentData
    {
        return new ComponentData($this->getType(), $this->getRawAttributes()['data'] ?? []);
    }

    /**
     * We override the `slot` attribute for a method call to ->getSlot() and then expose
     * the raw ->attributes['slot'] as ->__get('slotName')
     */
    public function __get($name)
    {
        if ($name === 'slot') {
            return $this->getSlot();
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
