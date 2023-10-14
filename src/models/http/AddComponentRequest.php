<?php

namespace markhuot\keystone\models\http;

use Craft;
use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\base\Model;
use yii\db\ActiveRecordInterface;

class AddComponentRequest extends Model
{
    public ElementInterface $element;

    public FieldInterface $field;

    public ?string $path;

    public ?string $slot;

    public int $sortOrder;

    public string $type;

    public function safeAttributes()
    {
        return [...parent::safeAttributes(), 'path', 'slot'];
    }

    public function rules(): array
    {
        return [
            ['element', 'required'],
            ['field', 'required'],
            ['sortOrder', 'required'],
            ['type', 'required'],
        ];
    }

    public function setAttributes($values, $safeOnly = true): void
    {
        $reflect = new \ReflectionClass($this);

        foreach ($reflect->getProperties() as $property) {
            $type = $property->getType()->getName();

            $isActiveRecord = class_exists($type) && class_implements($type, ActiveRecordInterface::class);
            $isElementInterface = $type === ElementInterface::class;
            $isFieldInterface = $type === FieldInterface::class;
            if (! $isActiveRecord && ! $isElementInterface && ! $isFieldInterface) {
                continue;
            }

            $condition = $values[$property->name];

            if (! is_array($condition)) {
                $condition = [(method_exists($type, 'primaryKey') ? $type::primaryKey() : 'id') => $condition];
            }

            if ((new \ReflectionClass($type))->implementsInterface(ElementInterface::class)) {
                $values[$property->name] = Craft::$app->getElements()->getElementById($condition['id']);
            } elseif ((new \ReflectionClass($type))->implementsInterface(FieldInterface::class)) {
                $values[$property->name] = Craft::$app->getFields()->getFieldById($condition['id']);
            } else {
                $values[$property->name] = $type::findOne($condition);
            }
        }

        parent::setAttributes($values, $safeOnly);
    }
}
