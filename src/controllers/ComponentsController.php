<?php

namespace markhuot\keystone\controllers;

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
use markhuot\keystone\models\http\StoreComponentRequest;
use yii\web\Request;

/**
 * @property Request|BodyParamObjectBehavior $request
 */
class ComponentsController extends Controller
{
    public function actionAdd()
    {
        $data = $this->request->getQueryParamObjectOrFail(AddComponentRequest::class);
        $parent = (new GetParentFromPath)->handle($data->element->id, $data->field->id, $data->path);

        return $this->asCpScreen()
            ->title('Add component')
            ->action('keystone/components/store')
            ->contentTemplate('keystone/select', [
                'element' => $data->element,
                'field' => $data->field,
                'path' => $data->path,
                'slot' => $data->slot,
                'parent' => $parent,
                'groups' => (new GetComponentType())->all()->groupBy(fn ($t) => $t->getCategory())->sortKeys(),
                'sortOrder' => $data->sortOrder,
            ]);
    }

    public function actionStore()
    {
        $data = $this->request->getBodyParamObjectOrFail(StoreComponentRequest::class);

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
        $component = $this->request->getQueryParamObjectOrFail(Component::class);
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
        $component = $this->request->getBodyParamObjectOrFail(Component::class);
        $fields = $this->request->getBodyParam('fields', []);

        (new EditComponentData)->handle($component, $fields);

        return $this->asSuccess('Component saved', [
            'fieldHtml' => $component->getElement()->getFieldHtml($component->getField()),
        ]);
    }

    public function actionDelete()
    {
        $component = $this->request->getBodyParamObjectOrFail(Component::class);
        (new DeleteComponent)->handle($component);

        return $this->asSuccess('Component deleted', [
            'fieldHtml' => $component->element->getFieldHtml($component->field),
        ]);
    }

    public function actionMove()
    {
        $data = $this->request->getBodyParamObjectOrFail(MoveComponentRequest::class);
        (new MoveComponent)->handle($data->source, $data->position, $data->target, $data->slot);

        return $this->asSuccess('Component moved', [
            'fieldHtml' => $data->getTargetElement()->getFieldHtml($data->getTargetField()),
        ]);
    }

    public function actionToggleDisclosure()
    {
        /** @var Component $component */
        $component = $this->request->getQueryParamObjectOrFail(Component::class);
        $defns = $component->getType()->getSlotDefinitions();
        $defaultState = $defns->every(fn ($d) => $d->isCollapsed()) ? 'closed' : 'open';
        $state = $component->disclosure->state ?? $defaultState;
        $newState = $state === 'open' ? 'closed' : 'open';

        if ($newState === $defaultState) {
            $component->disclosure->delete();
        } else {
            $component->disclosure->state = $newState;
            $component->disclosure->save();
        }

        return $this->asSuccess();
    }
}
