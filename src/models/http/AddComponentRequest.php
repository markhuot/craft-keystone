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

    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            ['element', 'required'],
            ['field', 'required'],
            ['sortOrder', 'required'],
            ['type', 'required'],
        ];
    }
}
