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
        $sortOrder = $this->request->getRequiredQueryParam('sortOrder');

        return $this->asCpScreen()
            ->title('Add component')
            ->action('keystone/components/store')
            ->contentTemplate('keystone/select', [
                'element' => $element,
                'field' => $field,
                'path' => $path,
                'slot' => $slot,
                'types' => (new GetComponentType())->all(),
                'sortOrder' => $sortOrder,
            ]);
    }

    public function actionStore()
    {
        return $this->asSuccess('Component added', [
            'path' => $this->request->getBodyParam('path'),
            'slot' => $this->request->getBodyParam('slot'),
            'type' => $this->request->getBodyParam('type'),
            'sortOrder' => $this->request->getBodyParam('sortOrder'),
        ]);
    }

    public function actionEdit()
    {
        $id = $this->request->getRequiredQueryParam('id');
        $elementId = $this->request->getRequiredQueryParam('elementId');

        return $this->asCpScreen()
            ->title('Edit component')
            ->tabs([
                ['label' => 'Content', 'url' => '#tab-content'],
                ['label' => 'Styles', 'url' => '#tab-styles'],
                ['label' => 'Admin', 'url' => '#tab-admin'],
            ])
            ->action('keystone/components/update')
            ->contentTemplate('keystone/edit', [
                'component' => Component::findOne(['id' => $id, 'elementId' => $elementId]),
            ]);
    }

    public function actionUpdate()
    {
        return $this->asSuccess('Component saved', [
            'action' => 'edit-component',
            'id' => $this->request->getRequiredBodyParam('id'),
            'elementId' => $this->request->getRequiredBodyParam('elementId'),
            'fieldId' => $this->request->getRequiredBodyParam('fieldId'),
            'fields' => $this->request->getRequiredBodyParam('fields'),
        ]);
    }

    public function actionDelete()
    {
        return $this->asSuccess('Component deleted', [
            'action' => 'delete-component',
            'id' => $this->request->getRequiredBodyParam('id'),
            'elementId' => $this->request->getRequiredBodyParam('elementId'),
            'fieldId' => $this->request->getRequiredBodyParam('fieldId'),
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
