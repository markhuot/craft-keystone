<?php

namespace markhuot\keystone\models\http;

use markhuot\keystone\base\Model;
use markhuot\keystone\models\Component;

class EditComponentRequest extends Model
{
    public Component $component;
}
