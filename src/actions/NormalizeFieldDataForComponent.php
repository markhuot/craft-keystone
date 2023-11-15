<?php

namespace markhuot\keystone\actions;

use Craft;
use markhuot\keystone\base\InlineEditData;
use markhuot\keystone\listeners\AttachFieldBehavior;
use markhuot\keystone\models\Component;

class NormalizeFieldDataForComponent
{
    public function __construct(
        protected Component $component
    ) {
    }

    public function handle(mixed $value, string $handle)
    {
        // Get the field from the component type
        $field = $this->component->getType()->getField($handle);

        // $field may be null if the field has been deleted from the twig code
        // but still remains in the database.
        // If possible normalize the data so the DB stored ID gets turned in to
        // a Query object, for example.
        $value = $field?->normalizeValue($value) ?? $value;

        // If the field supports object templates, render the string out
        if ($field?->getBehavior(AttachFieldBehavior::INTERACTS_WITH_KEYSTONE)) {
            if ($field->shouldRenderWithContext() && is_string($value)) {
                $value = Craft::$app->getView()->renderObjectTemplate($value, $this->component->getContext());
            }
        }

        // If the field is editable, return an editable div
//        if ($field?->getBehavior(AttachFieldBehavior::INTERACTS_WITH_KEYSTONE)) {
//            if ($field->isEditableInLivePreview() && Craft::$app->getRequest()->getQueryParam('x-craft-live-preview') !== null) {
//                return new InlineEditData($this->component, $field, $value);
//            }
//        }

        return $value;
    }
}
