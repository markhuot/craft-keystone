<?php

namespace markhuot\keystone\behaviors;

use yii\base\Behavior;

class InteractsWithKeystoneBehavior extends Behavior
{
    protected bool $editableInLivePreview = false;
    protected bool $renderWithContext = false;

    public function setEditableInLivePreview(bool $editable = true)
    {
        $this->editableInLivePreview = $editable;

        return $this->owner;
    }

    public function setRenderWithContext(bool $renderWithContext = true)
    {
        $this->renderWithContext = $renderWithContext;

        return $this->owner;
    }

    public function isEditableInLivePreview()
    {
        return $this->editableInLivePreview;
    }

    public function shouldRenderWithContext()
    {
        return $this->renderWithContext;
    }
}
