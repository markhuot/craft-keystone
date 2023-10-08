<?php

namespace markhuot\keystone\controllers;

use Craft;
use craft\web\Controller;
use markhuot\keystone\actions\AddComponent;
use markhuot\keystone\actions\GetComponentType;
use markhuot\keystone\models\Component;
use markhuot\keystone\models\ComponentData;
use markhuot\keystone\models\ComponentElement;
use yii\db\Expression;

class ComponentsController extends Controller
{
    public function actionAdd()
    {
        $elementId = $this->request->getRequiredQueryParam('elementId');
        $element = Craft::$app->elements->getElementById($elementId);
        $fieldId = $this->request->getRequiredQueryParam('fieldId');
        $field = Craft::$app->fields->getFieldById($fieldId);
        $path = $this->request->getQueryParam('path');
        $slot = $this->request->getQueryParam('slot');

        return $this->asCpScreen()
            ->title('Add component')
            ->action('keystone/components/store')
            ->contentTemplate('keystone/select', [
                'element' => $element,
                'field' => $field,
                'path' => $path,
                'slot' => $slot,
                'types' => (new GetComponentType())->all(),
            ]);
    }

    public function actionStore()
    {
        $componentData = new ComponentData;
        $componentData->type = $this->request->getRequiredBodyParam('type');
        $componentData->save();

        $component = new Component;
        $component->elementId = $elementId = $this->request->getRequiredBodyParam('elementId');
        $component->fieldId = $fieldId = $this->request->getRequiredBodyParam('fieldId');
        $component->dataId = $componentData->id;
        $component->path = $path = $this->request->getBodyParam('path');
        $component->slot = $this->request->getBodyParam('slot');
        $component->type = $this->request->getRequiredBodyParam('type');
        $component->sortOrder = ((Component::find()->where([
            'elementId' => $elementId,
            'fieldId' => $fieldId,
            'path' => $component->path,
            'slot' => $component->slot,
        ])->max('sortOrder')) ?? -1) + 1;
        $component->save();

        $element = Craft::$app->elements->getElementById($component->elementId);
        $field = Craft::$app->fields->getFieldById($component->fieldId);

        return $component->errors ?
            $this->asFailure('Oh no') :
            $this->asSuccess('Component added', [
                'elementId' => $component->elementId,
                'fieldId' => $component->fieldId,
                'fieldHandle' => $field->handle,
                'fieldHtml' => $field->getInputHtml(null, $element),
            ]);
    }

    public function actionEdit()
    {
        $id = $this->request->getRequiredQueryParam('id');

        return $this->asCpScreen()
            ->title('Edit component')
            ->tabs([
                ['label' => 'Content', 'url' => '#tab-content'],
                ['label' => 'Styles', 'url' => '#tab-styles'],
            ])
            ->action('keystone/components/update')
            ->contentTemplate('keystone/edit', [
                'component' => Component::findOne(['id' => $id]),
            ]);
    }

    public function actionUpdate()
    {
        $id = $this->request->getRequiredBodyParam('id');
        $data = $this->request->getBodyParam('fields', []);

        $component = Component::findOne(['id' => $id]);
        $component->data->merge($data);
        $component->data->save();

        $element = Craft::$app->elements->getElementById($component->elementId);
        $field = Craft::$app->fields->getFieldById($component->fieldId);

        return $this->asSuccess('Component saved', [
            'elementId' => $component->elementId,
            'fieldId' => $component->fieldId,
            'fieldHandle' => $field->handle,
            'fieldHtml' => $field->getInputHtml(null, $element),
        ]);
    }

    public function actionMove()
    {
        $sourceId = $this->request->getRequiredBodyParam('source');
        $source = Component::findOne(['id' => $sourceId]);
        $targetId = $this->request->getRequiredBodyParam('target');
        $target = Component::findOne(['id' => $targetId]);
        $position = $this->request->getRequiredBodyParam('position');

        if ($position === 'above') {
            Component::updateAll([
                'sortOrder' => new Expression('sortOrder + 1')
            ], ['and',
                ['=', 'elementId', $target->elementId],
                ['=', 'fieldId', $target->fieldId],
                ['=', 'path', $target->path],
                ['>=', 'sortOrder', $target->sortOrder]
            ]);
        }
        if ($position === 'below')
        {
            Component::updateAll([
                'sortOrder' => new Expression('sortOrder + 1')
            ], ['and',
                ['=', 'elementId', $target->elementId],
                ['=', 'fieldId', $target->fieldId],
                ['=', 'path', $target->path],
                ['>=', 'sortOrder', $target->sortOrder]
            ]);
        }

        $source->path = $target->path;
        $source->sortOrder = $position == 'above' ? $target->sortOrder : $target->sortOrder + 1;
        $source->save();

        $element = Craft::$app->elements->getElementById($source->elementId);
        $field = Craft::$app->fields->getFieldById($source->fieldId);

        return $this->asSuccess('Component moved', [
            'fieldHtml' => $field->getInputHtml(null, $element),
        ]);
    }

    public function actionGetEditModalHtml()
    {
        $id = $this->request->getRequiredBodyParam('id');
        $component = Component::findOne(['id' => $id]);

        return Craft::$app->getView()->renderTemplate('keystone/builder/edit', [
            'component' => $component,
        ]);
    }
}
