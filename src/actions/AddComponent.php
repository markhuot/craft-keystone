<?php

namespace markhuot\keystone\actions;

use craft\helpers\Db;
use craft\helpers\StringHelper;
use markhuot\keystone\db\Table;

class AddComponent
{
    public function handle(
        int $elementId,
        int $fieldId,
        int $sortOrder,
        string $path,
        string $slot,
        string $type
    ) {
        $level = count(explode('/', $path));
        $date = Db::prepareDateForDb(new \DateTime);

        \Craft::$app->getDb()->createCommand()->insert(Table::COMPONENTS, [
            'elementId' => $elementId,
            'fieldId' => $fieldId,
            'type' => $type,
            'sortOrder' => $sortOrder,
            'path' => $path,
            'level' => $level,
            'slot' => $slot,
            'dateCreated' => $date,
            'dateUpdated' => $date,
            'uid' => StringHelper::UUID(),
        ]);
    }
}
