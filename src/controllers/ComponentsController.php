<?php

namespace markhuot\keystone\controllers;

use Craft;
use craft\web\Controller;
use markhuot\keystone\actions\AddComponent;
use markhuot\keystone\actions\GetComponentType;
use markhuot\keystone\models\Component;

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
        $component = new Component;
        $component->elementId = $elementId = $this->request->getRequiredBodyParam('elementId');
        $component->fieldId = $fieldId = $this->request->getRequiredBodyParam('fieldId');
        $component->path = $path = $this->request->getBodyParam('path');
        $slot = $this->request->getBodyParam('slot');
        $slot = $slot === '' ? null : $slot;
        $component->slot = $slot;
        $component->type = $this->request->getRequiredBodyParam('type');
        $component->sortOrder = ((Component::find()->where([
            'elementId' => $elementId,
            'fieldId' => $fieldId,
            'path' => $path,
            'slot' => $slot,
        ])->max('sortOrder')) ?? -1) + 1;
        $component->save();

        return $component->errors ?
            $this->asFailure('Oh no') :
            $this->asSuccess('Component added');
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
        $component->data = array_merge($component->data->toArray(), $data);
        $component->save();

        return $this->asSuccess('Component saved');
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
