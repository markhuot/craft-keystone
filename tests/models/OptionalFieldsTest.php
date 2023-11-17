<?php

namespace markhuot\keystone\tests\models;

use markhuot\keystone\base\Model;
use markhuot\keystone\validators\Safe;

class OptionalFieldsTest extends Model
{
    public string $foo;

    #[Safe]
    public ?string $bar;

    public function rules(): array
    {
        return [
            [['foo'], 'required'],
        ];
    }
}
