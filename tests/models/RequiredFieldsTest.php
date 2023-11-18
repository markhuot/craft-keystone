<?php

namespace markhuot\keystone\tests\models;

use markhuot\keystone\base\Model;
use markhuot\keystone\validators\Required;

class RequiredFieldsTest extends Model
{
    #[Required]
    public $foo;
}
