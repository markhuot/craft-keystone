<?php

namespace markhuot\keystone\models\http;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use markhuot\keystone\base\Model;
use markhuot\keystone\validators\Required;
use markhuot\keystone\validators\Safe;

class StoreComponentRequest extends Model
{
    #[Required]
    public ElementInterface $element;

    #[Required]
    public FieldInterface $field;

    #[Safe]
    public ?string $path = null;

    #[Safe]
    public ?string $slot = null;

    #[Required]
    public int $sortOrder;

    #[Required]
    public string $type;
}
