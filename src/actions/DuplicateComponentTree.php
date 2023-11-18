<?php

namespace markhuot\keystone\actions;

use craft\base\ElementInterface;
use craft\helpers\DateTimeHelper;
use craft\helpers\StringHelper;
use markhuot\keystone\fields\Keystone;
use markhuot\keystone\models\Component;

use function markhuot\keystone\helpers\base\throw_if;

class DuplicateComponentTree
{
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
     */
    public function handle(ElementInterface $sourceElement, ElementInterface $destinationElement, Keystone $field): void
    {
        throw_if(empty($destinationElement->id), 'Destination element must be saved with an ID before its components can be duplicated');
        throw_if($field->id === null || ! is_int($field->id), 'The field must be saved with an ID before components can be duplicated');

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
            /** @var ?Component $source */
            $source = $sourceBatch->current() ?: null;
            /** @var ?Component $destination */
            $destination = $destinationBatch->current() ?: null;

            // if we've continued on past the end of our lists we can stop here
            if ($source === null && $destination === null) {
                break;
            }

            // insert source
            elseif ($source !== null && $destination === null) {
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

            // delete destination
            elseif ($source === null && $destination !== null) {
                Component::deleteAll([
                    'id' => $destination->id,
                    'elementId' => $destinationElement->id,
                    'fieldId' => $field->id,
                ]);

                $destinationBatch->next();
            }

            // if the IDs are the same we can update in place
            elseif ($source && $destination && $source->id === $destination->id) {
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
            elseif ($source && $destination && $source->id > $destination->id) {
                Component::deleteAll([
                    'id' => $destination->id,
                    'elementId' => $destinationElement->id,
                    'fieldId' => $field->id,
                ]);

                $destinationBatch->next();
            }

            // if the source ID is missing from the destination, insert it
            elseif ($source && $destination && $source->id < $destination->id) {
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
}
