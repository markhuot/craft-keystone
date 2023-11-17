<?php

namespace markhuot\keystone\tests\models;

use craft\base\ElementInterface;
use craft\base\Model;

class ElementToElementIdTest extends Model
{
    public ElementInterface $element;

    public function rules(): array
    {
        return [
            [['element'], 'required'],
        ];
    }
}
