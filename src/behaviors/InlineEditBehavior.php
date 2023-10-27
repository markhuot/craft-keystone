<?php

namespace markhuot\keystone\behaviors;

use yii\base\Behavior;

class InlineEditBehavior extends Behavior
{
    protected bool $editableInLivePreview = false;

    public function setEditableInLivePreview(bool $editable = true)
    {
        $this->editableInLivePreview = $editable;
    }

    public function isEditableInLivePreview()
    {
        return $this->editableInLivePreview;
    }
}
