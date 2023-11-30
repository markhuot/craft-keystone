<?php

namespace markhuot\keystone\models\http;

use Craft;
use craft\base\ElementInterface;
use craft\base\FieldInterface;
use markhuot\keystone\base\Model;
use markhuot\keystone\enums\MoveComponentPosition;
use markhuot\keystone\models\Component;
use markhuot\keystone\validators\Required;
use markhuot\keystone\validators\Safe;

use function markhuot\keystone\helpers\base\app;
use function markhuot\keystone\helpers\base\throw_if;

class MoveComponentRequest extends Model
{
    #[Required]
    public Component $source;

    #[Required]
    public Component $target;

    #[Required]
    public MoveComponentPosition $position;

    #[Safe]
    public ?string $slot = null;

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
        throw_if($this->target->fieldId === null, 'Could not find a field with null ID');

        $field = app()->getFields()->getFieldById($this->target->fieldId);
        throw_if($field === null, 'Could not find a field with the ID '.$this->target->fieldId);

        return $field;
    }
}
