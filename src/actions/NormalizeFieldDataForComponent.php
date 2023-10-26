<?php

namespace markhuot\keystone\actions;

use craft\base\FieldInterface;
use markhuot\keystone\base\InlineEditData;
use markhuot\keystone\models\Component;

class NormalizeFieldDataForComponent
{
    public function __construct(
        protected Component $component
    ) { }

    public function handle(mixed $value, string $handle)
    {
        // Get the field from the component type
        $field = $this->component->getType()->getField($handle);

        // $field may be null if the field has been deleted from the twig code
        // but still remains in the database.
        // If possible normalize the data so the DB stored ID gets turned in to
        // a Query object, for example.
        $value = $field?->normalizeValue($value) ?? $value;

        if ($field?->getBehavior('inlineEdit')) {
            if ($field->isEditableInPreview()) {
                return new InlineEditData($this->component, $handle, $value);
            }
        }

        // @TODO add in logic to return a custom class here
        // the custom class will be responsible for rendering the
        // value in normal situations or a live editor in
        // live preview situations.
        // We'll monkey patch on a behavior to the PlainText field
        // type to make this possible
        // if ($this->getType()->getHandle() === 'site/components/tab' && $handle === 'description') {
        //     return 'foo';
        // }

        return $value;
    }
}
