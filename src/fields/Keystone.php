<?php

namespace markhuot\keystone\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\web\View;
use markhuot\keystone\actions\GetComponentType;
use markhuot\keystone\models\Component;
use markhuot\keystone\models\ComponentElement;
use Twig\Markup;

class Keystone extends Field
{
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

    protected function inputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate('keystone/field', [
            'element' => $element,
            'field' => $this,
            'component' => $this->getFragment($element),
            'getComponentTypes' => new GetComponentType,
        ]);
    }

    public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        return $this->getFragment($element);
    }
}
