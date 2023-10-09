<?php

namespace markhuot\keystone\actions;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\DateTimeHelper;
use craft\helpers\StringHelper;
use markhuot\keystone\db\Table;
use markhuot\keystone\fields\Keystone;
use markhuot\keystone\models\Component;

class DuplicateComponentTree
{
    static array $mapping = [];

    public function handle(ElementInterface $source, ElementInterface $destination, Keystone $field)
    {
        $query = Component::find()->where([
            'elementId' => $source->id,
            'fieldId' => $field->id,
        ])->orderBy(['path' => 'asc']);

        foreach ($query->each() as $component) {
            $duplicate = new Component;
            $duplicate->id = $component->id;
            $duplicate->elementId = $destination->id;
            $duplicate->fieldId = $field->id;
            $duplicate->dataId = $component->dataId;
            $duplicate->sortOrder = $component->sortOrder;
            $duplicate->path = $this->remapPath($component->path);
            $duplicate->level = $component->level;
            $duplicate->slot = $component->slot;
            $duplicate->dateCreated = DateTimeHelper::now();
            $duplicate->dateUpdated = DateTimeHelper::now();
            $duplicate->uid = StringHelper::UUID();
            $duplicate->save();

            static::$mapping[$component->id] = $duplicate->id;
        }

        // I'd love to use INSERT INTO components (elementId, fieldId, ...) SELECT * FROM components WHERE elementId= and fieldId=
        // but Craft breaks this because they subclass the db->createCommand() and don't allow you to pass
        // a query as the second parameter. They look for $columns[dateCreated] on that second param which
        // works when setting raw values but not if a query is passed because it tries to $query[dateCreated]
        // and Query isn't array-accessible.
        //
        // $query = Component::find()->select(['fieldId', 'componentId', 'sortOrder', 'path', 'level', 'slot'])->where([
        //     'elementId' => $source->id,
        //     'fieldId' => $field->id,
        // ]);
        // $params = [
        //     'elementId' => $source->id,
        //     'fieldId' => $field->id,
        // ];
        // Craft::$app->db->createCommand(Craft::$app->db->getQueryBuilder()->insert(Table::COMPONENTS, $query, $params))->execute();
        // Craft::$app->db->createCommand()->insert(Table::COMPONENTS, $query);
    }

    protected function remapPath(?string $path)
    {
        if ($path === null) {
            return null;
        }

        return collect(explode('/', $path))
            ->map(function ($segment) use ($path) {
                if (! isset(static::$mapping[$segment])) {
                    throw new \RuntimeException('Could not remap ' . $path . ' because ' . $segment . ' could not be found');
                }

                return static::$mapping[$segment];
            })
            ->join('/');
    }
}
