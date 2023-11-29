<?php

namespace markhuot\keystone\controllers;

use markhuot\keystone\models\Component;
use yii\web\Response;

class Controller extends \craft\web\Controller
{
    public function asFieldSuccess(string $message, ?Component $component): Response
    {
        $html = $component->getElement()->getFieldHtml($component->getField());

        return $this->asSuccess($message, ['fieldHtml' => $html]);
    }
}
