<?php

namespace markhuot\keystone\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\web\View;
use markhuot\keystone\actions\AddComponent;
use markhuot\keystone\actions\DuplicateComponentTree;
use markhuot\keystone\actions\EditComponentData;
use markhuot\keystone\actions\GetComponentType;
use markhuot\keystone\actions\MoveComponent;
use markhuot\keystone\listeners\OverrideDraftResponseWithFieldHtml;
use markhuot\keystone\models\Component;
use Twig\Markup;

class Keystone extends Field
{
    /**
     * @inheritDoc
     */
    public static function hasContentColumn(): bool
    {
        return false;
    }

    /**
     * Gets a fragment containing all the components of the current element for this field instance
     */
    protected function getFragment(ElementInterface $element)
    {
        $component = new Component;
        $component->type = 'keystone/fragment';
        $component->setSlotted(Component::find()->where([
            'elementId' => $element->id,
            'fieldId' => $this->id,
        ])->orderBy('sortOrder')->all());

        return $component;
    }

    /**
     * @inheritDoc
     */
    public function normalizeValueFromRequest(mixed $value, ?ElementInterface $element = null): mixed
    {
        if (($value['action'] ?? false) === false) {
            return null;
        }

        $payload = json_decode($value['action'], true, 512, JSON_THROW_ON_ERROR);

        if ($payload['name'] === 'add-component') {
            ['sortOrder' => $sortOrder, 'path' => $path, 'slot' => $slot, 'type' => $type] = $payload;
            (new AddComponent)->handle($element->id, $this->id, $sortOrder, $path, $slot, $type);
            OverrideDraftResponseWithFieldHtml::override($element, $this);
        }

        if ($payload['name'] === 'move-component') {
            ['source' => $sourceId, 'target' => $targetId, 'position' => $position] = $payload;
            $source = Component::findOne(['id' => $sourceId, 'elementId' => $element->id]);
            $target = Component::findOne(['id' => $targetId, 'elementId' => $element->id]);
            (new MoveComponent)->handle($source, $target, $position);
            OverrideDraftResponseWithFieldHtml::override($element, $this);
        }

        if ($payload['name'] === 'edit-component') {
            ['id' => $id, 'elementId' => $elementId, 'fields' => $fields] = $payload;
            $component = Component::findOne(['id' => $id, 'elementId' => $elementId]);
            (new EditComponentData)->handle($component, $fields);
            OverrideDraftResponseWithFieldHtml::override($element, $this);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function inputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate('keystone/field', [
            'element' => $element,
            'field' => $this,
            'component' => $this->getFragment($element),
            'getComponentTypes' => new GetComponentType,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        return $this->getFragment($element);
    }

    /**
     * @inheritDoc
     */
    public function afterElementSave(ElementInterface $element, bool $isNew): void
    {
        // If we're duplicating an element to create a draft or revision, duplicate the component
        // tree as well
        if ($element->duplicateOf && $isNew) {
            (new DuplicateComponentTree)->handle($element->duplicateOf, $element, $this);
        }

        parent::afterElementSave($element, $isNew);
    }
}
