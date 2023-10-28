<?php

namespace markhuot\keystone\controllers;

use Craft;
use craft\web\Controller;
use markhuot\keystone\actions\AddComponent;
use markhuot\keystone\actions\DeleteComponent;
use markhuot\keystone\actions\EditComponentData;
use markhuot\keystone\actions\GetComponentType;
use markhuot\keystone\actions\GetParentFromPath;
use markhuot\keystone\actions\MoveComponent;
use markhuot\keystone\behaviors\BodyParamObjectBehavior;
use markhuot\keystone\models\Component;
use markhuot\keystone\models\http\AddComponentRequest;
use markhuot\keystone\models\http\MoveComponentRequest;
use yii\web\Request;

/**
 * @property Request|BodyParamObjectBehavior $request
 */
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
        $parent = (new GetParentFromPath)->handle($elementId, $fieldId, $path);

        return $this->asCpScreen()
            ->title('Add component')
            ->action('keystone/components/store')
            ->contentTemplate('keystone/select', [
                'element' => $element,
                'field' => $field,
                'path' => $path,
                'slot' => $slot,
                'parent' => $parent,
                'groups' => (new GetComponentType())->all()->groupBy(fn ($t) => $t->getCategory()),
                'sortOrder' => $sortOrder,
            ]);
    }

    public function actionStore()
    {
        $data = $this->request->getBodyParamObject(AddComponentRequest::class);

        (new AddComponent)->handle(
            elementId: $data->element->id,
            fieldId: $data->field->id,
            sortOrder: $data->sortOrder,
            path: $data->path,
            slotName: $data->slot,
            type: $data->type,
        );

        return $this->asSuccess('Component added', [
            'fieldHtml' => $data->element->getFieldHtml($data->field),
        ]);
    }

    public function actionEdit()
    {
        $id = $this->request->getRequiredQueryParam('id');
        $elementId = $this->request->getRequiredQueryParam('elementId');
        $fieldId = $this->request->getRequiredQueryParam('fieldId');
        $component = Component::findOne(['id' => $id, 'elementId' => $elementId, 'fieldId' => $fieldId]);
        $hasContentFields = $component->getType()->getFieldDefinitions()->isNotEmpty();

        return $this->asCpScreen()
            ->title('Edit component')
            ->tabs(array_filter([
                $hasContentFields ? ['label' => 'Content', 'url' => '#tab-content'] : null,
                ['label' => 'Design', 'url' => '#tab-design'],
                ['label' => 'Admin', 'url' => '#tab-admin'],
            ]))
            ->action('keystone/components/update')
            ->contentTemplate('keystone/edit', [
                'component' => $component,
            ]);
    }

    public function actionUpdate()
    {
        $component = $this->request->getBodyParamObject(Component::class);
        $fields = $this->request->getBodyParam('fields', []);

        (new EditComponentData)->handle($component, $fields);

        return $this->asSuccess('Component saved');
    }

    public function actionDelete()
    {
        $component = $this->request->getBodyParamObject(Component::class);
        (new DeleteComponent)->handle($component);

        return $this->asSuccess('Component deleted', [
            'fieldHtml' => $component->element->getFieldHtml($component->field),
        ]);
    }

    public function actionMove()
    {
        $data = $this->request->getBodyParamObject(MoveComponentRequest::class);
        (new MoveComponent)->handle($data->source, $data->position, $data->target, $data->slot);

        return $this->asSuccess('Component moved', [
            'fieldHtml' => $data->getTargetElement()->getFieldHtml($data->getTargetField()),
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
