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

    /**
     * The goal of this method is to performantly scan through two sets of components and copy the
     * components from the source in to the destination. We do this by looping over the source and
     * destination components in a single loop. We compare the ordered IDs and either create, update
     * or delete them out of the destination.
     *
     * There's _a lot_ of extra code here just to make sure we're not deleting components that don't
     * actually need to be deleted. Instead we're just bumping their "dateUpdated" so we get accurate
     * tracking of changes.
     *
     * source: [1,4,5,7]
     * destination: [1,2,3,4,6,7,8]
     * 1==1 // nothing, advance both
     * 2<4 // delete 2, advance destination
     * 3<4 // delete 3, advance destination
     * 4==4 // advance both
     * 6>5 // insert five in to destination, advance source
     * 6<7 // delete six, advance source
     * 7==7 // advance both
     * 8==null // delete 8, advance destination
     *
     * destination: [1,2]
     * source: [3,4]
     * 1<3 // delete 1, advance source
     * 2<3 // delete 2, advance source
     * null!=3 // insert 3, advance source
     * null!=4 // insert 4, advance source
     *
     */
    public function handle(ElementInterface $sourceElement, ElementInterface $destinationElement, Keystone $field)
    {
        $sourceQuery = Component::find()->where([
            'elementId' => $sourceElement->id,
            'fieldId' => $field->id,
        ])->orderBy(['id' => 'asc']);
        $sourceBatch = $sourceQuery->each();
        $sourceBatch->next();

        $destinationQuery = Component::find()->where([
            'elementId' => $destinationElement->id,
            'fieldId' => $field->id,
        ])->orderBy(['id' => 'asc']);
        $destinationBatch = $destinationQuery->each();
        $destinationBatch->next();

        while (true) {
            /** @var Component $source */
            $source = $sourceBatch->current();
            /** @var Component $destination */
            $destination = $destinationBatch->current();

            // if we've continued on past the end of our lists we can stop here
            if ($source === false && $destination === false) {
                break;
            }

            else if ($source !== false && $destination === false) {
                // insert source
                \markhuot\craftpest\helpers\test\dump(2);

                $new = new Component;
                $new->id = $source->id;
                $new->elementId = $destinationElement->id;
                $new->fieldId = $field->id;
                $new->dataId = $source->dataId;
                $new->sortOrder = $source->sortOrder;
                $new->path = $source->path;
                $new->level = $source->level;
                $new->slot = $source->slot;
                $new->dateCreated = DateTimeHelper::now();
                $new->dateUpdated = DateTimeHelper::now();
                $new->uid = StringHelper::UUID();
                $new->save();

                $sourceBatch->next();
            }

            else if ($source === false && $destination !== false) {
                // delete destination
                \markhuot\craftpest\helpers\test\dump(3);
                Component::deleteAll([
                    'id' => $destination->id,
                    'elementId' => $destinationElement->id,
                    'fieldId' => $field->id,
                ]);


                $destinationBatch->next();
            }

            // if the IDs are the same we can update in place
            else if ($source->id === $destination->id) {
                \markhuot\craftpest\helpers\test\dump(4);
                $destination->dataId = $source->dataId;
                $destination->sortOrder = $source->sortOrder;
                $destination->path = $source->path;
                $destination->level = $source->level;
                $destination->slot = $source->slot;
                $destination->save();

                $sourceBatch->next();
                $destinationBatch->next();
            }

            // if the destination ID is missing from the source, delete it
            else if ($source->id > $destination->id) {
                \markhuot\craftpest\helpers\test\dump(5);
                Component::deleteAll([
                    'id' => $destination->id,
                    'elementId' => $destinationElement->id,
                    'fieldId' => $field->id,
                ]);

                $destinationBatch->next();
            }

            // if the source ID is missing from the destination, insert it
            else if ($source->id < $destination->id) {
                \markhuot\craftpest\helpers\test\dump(6);
                $new = new Component;
                $new->id = $source->id;
                $new->elementId = $destinationElement->id;
                $new->fieldId = $field->id;
                $new->dataId = $source->dataId;
                $new->sortOrder = $source->sortOrder;
                $new->path = $source->path;
                $new->level = $source->level;
                $new->slot = $source->slot;
                $new->dateCreated = DateTimeHelper::now();
                $new->dateUpdated = DateTimeHelper::now();
                $new->uid = StringHelper::UUID();
                $new->save();

                $sourceBatch->next();
            }
        }
    }

    /**
     * @deprecated
     */
    public function simpleHandle(ElementInterface $source, ElementInterface $destination, Keystone $field)
    {
        // Delete existing components since the duplicated components will replace them
        Craft::$app->db->createCommand()->delete(Table::COMPONENTS, [
            'elementId' => $destination->id,
            'fieldId' => $field->id,
        ])->execute();

        $query = Component::find()->where([
            'elementId' => $source->id,
            'fieldId' => $field->id,
        ])->orderBy(['path' => 'asc', 'sortOrder' => 'asc']);

        foreach ($query->each() as $existing) {
            $duplicate = new Component;
            $duplicate->id = $existing->id;
            $duplicate->elementId = $destination->id;
            $duplicate->fieldId = $field->id;
            $duplicate->dataId = $existing->dataId;
            $duplicate->sortOrder = $existing->sortOrder;
            $duplicate->path = $existing->path;
            $duplicate->level = $existing->level;
            $duplicate->slot = $existing->slot;
            $duplicate->dateCreated = DateTimeHelper::now();
            $duplicate->dateUpdated = DateTimeHelper::now();
            $duplicate->uid = StringHelper::UUID();
            $duplicate->save();
        }
    }
}
