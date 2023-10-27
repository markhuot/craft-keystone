<?php

namespace markhuot\keystone\base;

use craft\base\FieldInterface;
use craft\web\View;
use markhuot\keystone\models\Component;

class InlineEditData
{
    public function __construct(
        protected Component $component,
        protected FieldInterface $field,
        protected ?string $value
    ) {
    }

    public function __toString()
    {
        return \Craft::$app->getView()->renderTemplate('keystone/inline-edit', [
            'component' => $this->component,
            'field' => $this->field,
            'value' => $this->value,
        ], View::TEMPLATE_MODE_CP);
    }
}
