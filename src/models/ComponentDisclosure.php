<?php

namespace markhuot\keystone\models;

use ArrayAccess;
use Closure;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use Illuminate\Support\Collection;
use markhuot\keystone\base\Attribute;
use markhuot\keystone\base\FieldDefinition;
use markhuot\keystone\db\ActiveRecord;
use markhuot\keystone\db\Table;

use function markhuot\keystone\helpers\base\throw_if;
use function markhuot\keystone\helpers\data\data_forget;

/**
 */
class ComponentDisclosure extends ActiveRecord
{
    public static function tableName()
    {
        return Table::COMPONENT_DISCLOSURES;
    }
}
