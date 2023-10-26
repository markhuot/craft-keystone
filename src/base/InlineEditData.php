<?php

namespace markhuot\keystone\base;

use craft\web\View;
use markhuot\keystone\models\Component;

class InlineEditData
{
    public function __construct(
        protected Component $component,
        protected string $handle,
        protected string $value
    ) { }

    public function __toString()
    {
        return \Craft::$app->getView()->renderTemplate('keystone/inline-edit', [
            'component' => $this->component,
            'handle' => $this->handle,
            'value' => $this->value,
        ], View::TEMPLATE_MODE_CP);
    }
}
