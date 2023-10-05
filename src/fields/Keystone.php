<?php

namespace markhuot\keystone\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\web\View;
use markhuot\keystone\actions\GetComponentTypes;
use markhuot\keystone\models\Component;
use Twig\Markup;

class Keystone extends Field
{
    protected function inputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate('keystone/field', [
            'element' => $element,
            'field' => $this,
            'components' => Component::find()->where(['elementId' => $element->id, 'fieldId' => $this->id])->orderBy(['path' => 'asc', 'sortOrder' => 'asc'])->all(),
            'getComponentTypes' => new GetComponentTypes,
        ]);
    }

    public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        $component = new Component;
        $component->elementId = $element->id;
        $component->fieldId = $this->id;
        $component->sortOrder = 0;
        $component->level = 0;
        $component->slot = 'slot';
        $component->type = 'keystone/fragment';

        return new Markup($component->render(), 'utf-8');

//        $componentData = Component::findAll(['elementId' => $element->id, 'fieldId' => $this->id]);
//
//        return new Markup(\Craft::$app->getView()->renderTemplate('keystone/_slot', [
//            'component' => null,
//            'components' => Component::findAll(['elementId' => $element->id, 'fieldId' => $this->id, 'level' => 0]),
//        ], View::TEMPLATE_MODE_CP), 'utf-8');

//        $html = [];
//        $componentData = Component::findAll(['elementId' => $element->id, 'fieldId' => $this->id]);
//
//        foreach ($componentData as $data) {
//            $component = (new GetComponentTypes)->handle()->first(fn ($component) => $component->getType() === $data['type']);
//            $component->load(['component' => $data]);
//            $html[] = $component->render();
//        }
//
//        return new Markup(implode(PHP_EOL, $html), 'utf-8');
    }
}
