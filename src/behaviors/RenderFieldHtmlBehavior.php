<?php

namespace markhuot\keystone\behaviors;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\fieldlayoutelements\CustomField;
use yii\base\Behavior;

/**
 * @property ElementInterface $owner
 */
class RenderFieldHtmlBehavior extends Behavior
{
    public function getFieldHtml(FieldInterface $field)
    {
        foreach ($this->owner->getFieldLayout()->createForm($this->owner)->tabs as $tab) {
            foreach ($tab->elements as [$fieldLayout, $isConditional, $fieldHtml]) {
                if ($fieldLayout instanceof CustomField) {
                    if ($fieldLayout->getField()->handle === $field->handle) {
                        return $fieldHtml;
                    }
                }
            }
        }

        throw new \RuntimeException('Could not find the '.$field->handle.' field on element '.$this->owner->id);
    }
}
