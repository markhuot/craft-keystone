<?php

namespace markhuot\keystone\behaviors;

use yii\base\Behavior;

class InlineEditBehavior extends Behavior
{
    protected bool $editableInPreview = false;

    public function setEditableInPreview(bool $editable=true)
    {
        $this->editableInPreview = $editable;
    }

    public function isEditableInPreview()
    {
        return $this->editableInPreview;
    }
}
