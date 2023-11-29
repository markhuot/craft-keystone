<?php

namespace markhuot\keystone\models;

use markhuot\keystone\db\ActiveRecord;
use markhuot\keystone\db\Table;

class ComponentDisclosure extends ActiveRecord
{
    public static function tableName()
    {
        return Table::COMPONENT_DISCLOSURES;
    }
}
