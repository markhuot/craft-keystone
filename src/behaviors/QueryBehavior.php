<?php

namespace markhuot\keystone\behaviors;

use yii\base\Behavior;

class QueryBehavior extends Behavior
{
    protected mixed $value;

    public function setCondition(mixed $value)
    {
        $this->value = $value;
    }

    public function getCondition()
    {
        return $this->value;
    }
}
