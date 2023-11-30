<?php

namespace markhuot\keystone\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use markhuot\keystone\actions\DuplicateComponentTree;
use markhuot\keystone\actions\GetComponentType;
use markhuot\keystone\models\Component;
use markhuot\keystone\models\ComponentData;
use function markhuot\keystone\helpers\base\app;
use function markhuot\keystone\helpers\base\throw_if;

/**
 * @property int|null $id remove string from the type hint because php will coerce it to an int
 */
class Keystone extends Field
{
    /**
     * {@inheritDoc}
     */
    public static function hasContentColumn(): bool
    {
        return false;
    }

    /**
     * Gets a fragment containing all the components of the current element for this field instance
     */
    protected function getFragment(?ElementInterface $element): Component
    {
        $component = new Component;
        $component->fieldId = $this->id;
        $component->elementId = $element?->id;
        $component->populateRelation('data', new ComponentData);
        $component->data->type = 'keystone/fragment';

        return $component;
    }

    /**
     * {@inheritDoc}
     */
    protected function inputHtml(mixed $value, ElementInterface $element = null): string
    {
        return app()->getView()->renderTemplate('keystone/field', [
            'element' => $element,
            'field' => $this,
            'component' => $this->getFragment($element)->withDisclosures(),
            'getComponentTypes' => new GetComponentType,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function normalizeValue(mixed $value, ElementInterface $element = null): mixed
    {
        // If the value has already been normalized, return it
        if ($value instanceof Component) {
            return $value;
        }

        // Otherwise fetch the components out of the database
        return $this->getFragment($element);
    }

    /**
     * {@inheritDoc}
     */
    public function afterElementSave(ElementInterface $element, bool $isNew): void
    {
        // If we're duplicating an element to create a draft or revision, duplicate the component
        // tree as well
        if ($element->duplicateOf && ($element->isNewForSite || $element->updatingFromDerivative)) {
            (new DuplicateComponentTree)->handle($element->duplicateOf, $element, $this);
        }

        parent::afterElementSave($element, $isNew);
    }
}
