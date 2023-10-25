<?php

namespace markhuot\keystone\models\http;

use Craft;
use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\base\Model;
use markhuot\keystone\models\Component;

use function markhuot\keystone\helpers\base\app;
use function markhuot\keystone\helpers\base\throw_if;

class MoveComponentRequest extends Model
{
    public Component $source;

    public Component $target;

    public string $position;

    public ?string $slot = null;

    /**
     * @return array<string>
     */
    public function safeAttributes(): array
    {
        return [...parent::safeAttributes(), 'slot'];
    }

    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            ['source', 'required'],
            ['target', 'required'],
            ['position', 'required'],
        ];
    }

    public function getTargetElement(): ElementInterface
    {
        // We ignore the next line for phpstan because Craft types on the second argument
        // which is looking for an element type class-string. But we want our components
        // to work on _any_ element type so we don't want to pass anything as the second
        // argument. Because of that phpstan can't reason about the template.
        // @phpstan-ignore-next-line
        $element = app()->getElements()->getElementById($this->target->elementId);
        throw_if($element === null, 'Could not find an element with the ID '.$this->target->elementId);

        return $element;
    }

    public function getTargetField(): FieldInterface
    {
        $field = app()->getFields()->getFieldById($this->target->fieldId);
        throw_if($field === null, 'Could not find a field with the ID '.$this->target->fieldId);

        return $field;
    }
}
