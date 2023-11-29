<?php

namespace markhuot\keystone\migrations;

use craft\db\Migration;
use markhuot\keystone\db\Table;

class Install extends Migration
{
    public function safeUp()
    {
        $this->createTable(Table::COMPONENT_DATA, [
            'id' => $this->primaryKey(),
            'type' => $this->string(256),
            'data' => $this->json(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(Table::COMPONENTS, [
            'id' => $this->integer(),
            'elementId' => $this->integer(),
            'fieldId' => $this->integer(),
            'dataId' => $this->integer(),
            'sortOrder' => $this->integer(),
            'path' => $this->string(1024),
            'level' => $this->integer(),
            'slot' => $this->string(256),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(Table::COMPONENT_DISCLOSURES, [
            'id' => $this->primaryKey(),
            'userId' => $this->integer(),
            'componentId' => $this->integer(),
            'state' => $this->enum('state', ['open', 'closed']),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, Table::COMPONENTS, ['id', 'elementId']);
        $this->addForeignKey(null, Table::COMPONENTS, ['elementId'], \craft\db\Table::ELEMENTS, ['id'], 'CASCADE', null);
        $this->addForeignKey(null, Table::COMPONENTS, ['fieldId'], \craft\db\Table::FIELDS, ['id'], 'CASCADE', null);
        $this->addForeignKey(null, Table::COMPONENTS, ['dataId'], Table::COMPONENT_DATA, ['id'], 'CASCADE', null);
        $this->addForeignKey(null, Table::COMPONENT_DISCLOSURES, ['userId'], \craft\db\Table::USERS, ['id'], 'CASCADE', null);
        $this->addForeignKey(null, Table::COMPONENT_DISCLOSURES, ['componentId'], Table::COMPONENTS, ['id'], 'CASCADE', null);

        return true;
    }

    public function safeDown()
    {
        $this->dropTableIfExists(Table::COMPONENT_DISCLOSURES);
        $this->dropTableIfExists(Table::COMPONENTS);
        $this->dropTableIfExists(Table::COMPONENT_DATA);

        return true;
    }
}
