<?php

namespace markhuot\keystone\controllers;

use Craft;
use craft\web\Controller;
use markhuot\keystone\actions\AddComponent;
use markhuot\keystone\models\Component;

class ComponentsController extends Controller
{
    public function actionAdd()
    {
        $component = Craft::$app->getRequest()->getBodyParamObject(Component::class);
        $component->save();
    }

    public function actionSetData()
    {
        $id = $this->request->getRequiredBodyParam('id');
        $key = $this->request->getRequiredBodyParam('key');
        $value = $this->request->getRequiredBodyParam('value');

        $component = Component::findOne(['id' => $id]);
        $component->data = [$key => $value];
        $component->save();
    }

    public function actionEdit()
    {
        $id = $this->request->getRequiredQueryParam('id');

        return $this->asCpScreen()
            ->title('Foo bar')
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
