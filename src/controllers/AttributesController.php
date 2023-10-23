<?php

namespace markhuot\keystone\controllers;

use Craft;
use craft\web\Controller;
use markhuot\keystone\models\Component;

class AttributesController extends Controller
{
    public function actionAdd()
    {
        $id = $this->request->getRequiredBodyParam('id');
        $elementId = $this->request->getRequiredBodyParam('elementId');
        $fieldId = $this->request->getRequiredBodyParam('fieldId');
        $attributeType = $this->request->getRequiredBodyParam('attributeType');
        $namespace = $this->request->headers->get('X-Craft-Namespace');

        /** @var Component $component */
        $component = Component::find()->where(['id' => $id, 'elementId' => $elementId, 'fieldId' => $fieldId])->one();
        $component->data->merge(['_attributes' => [$attributeType => null]])->save();

        Craft::$app->getView()->setNamespace($namespace);
        $html = Craft::$app->getView()->renderTemplate('keystone/edit/design', [
            'namespace' => $namespace,
            'component' => $component,
        ]);
        $html = Craft::$app->getView()->namespaceInputs($html, $namespace);

        return $this->asSuccess('Attribute added!', [
            'html' => $html,
            'headHtml' => Craft::$app->getView()->getHeadHtml(),
            'bodyHtml' => Craft::$app->getView()->getBodyHtml(),
        ]);
    }

    public function actionDelete()
    {
        $id = $this->request->getRequiredBodyParam('id');
        $elementId = $this->request->getRequiredBodyParam('elementId');
        $fieldId = $this->request->getRequiredBodyParam('fieldId');
        $attributeType = $this->request->getRequiredBodyParam('attributeType');
        $namespace = $this->request->headers->get('X-Craft-Namespace');

        /** @var Component $component */
        $component = Component::find()->where(['id' => $id, 'elementId' => $elementId, 'fieldId' => $fieldId])->one();
        $component->data->forget('_attributes.'.$attributeType);
        $component->data->save();

        Craft::$app->getView()->setNamespace($namespace);
        $html = Craft::$app->getView()->renderTemplate('keystone/edit/design', [
            'namespace' => $namespace,
            'component' => $component,
        ]);
        $html = Craft::$app->getView()->namespaceInputs($html, $namespace);

        return $this->asSuccess('Attribute deleted!', [
            'html' => $html,
            'headHtml' => Craft::$app->getView()->getHeadHtml(),
            'bodyHtml' => Craft::$app->getView()->getBodyHtml(),
        ]);
    }
}
