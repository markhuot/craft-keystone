<?php

namespace markhuot\keystone\models\http;

use Craft;
use craft\base\ElementInterface;
use craft\base\Model;
use markhuot\keystone\models\Component;
use yii\db\ActiveRecordInterface;

class MoveComponentRequest extends Model
{
    public Component $source;

    public Component $target;

    public string $position;

    public ?string $slot = null;

    public function safeAttributes()
    {
        return [...parent::safeAttributes(), 'slot'];
    }

    public function rules(): array
    {
        return [
            ['source', 'required'],
            ['target', 'required'],
            ['position', 'required'],
        ];
    }

    public function getTargetElement()
    {
        return Craft::$app->getElements()->getElementById($this->target->elementId);
    }

    public function getTargetField()
    {
        return Craft::$app->getFields()->getFieldById($this->target->fieldId);
    }

    public function setAttributes($values, $safeOnly = true): void
    {
        $reflect = new \ReflectionClass($this);

        foreach ($reflect->getProperties() as $property) {
            $type = $property->getType()->getName();

            $isActiveRecord = class_exists($type) && class_implements($type, ActiveRecordInterface::class);
            $isElementInterface = $type === ElementInterface::class;
            if (! $isActiveRecord && ! $isElementInterface) {
                continue;
            }

            $condition = $values[$property->name];

            if (! is_array($condition)) {
                $condition = [(method_exists($type, 'primaryKey') ? $type::primaryKey() : 'id') => $condition];
            }

            if ((new \ReflectionClass($type))->implementsInterface(ElementInterface::class)) {
                $values[$property->name] = Craft::$app->elements->getElementById($condition['id']);
            } else {
                $values[$property->name] = $type::findOne($condition);
            }
        }

        parent::setAttributes($values, $safeOnly);
    }
}
