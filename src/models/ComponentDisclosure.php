<?php

namespace markhuot\keystone\models;

use markhuot\keystone\db\ActiveRecord;
use markhuot\keystone\db\Table;

/**
 * @property ?int $userId
 * @property int $componentId
 * @property string $state
 */
class ComponentDisclosure extends ActiveRecord
{
    public static function tableName()
    {
        return Table::COMPONENT_DISCLOSURES;
    }
}
