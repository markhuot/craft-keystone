<?php

namespace markhuot\keystone\actions;

use craft\elements\Asset;
use craft\elements\Entry;
use craft\fields\Assets;
use craft\fields\Entries;
use Illuminate\Support\Collection;
use markhuot\keystone\interfaces\ShouldHandleEvents;
use markhuot\keystone\models\Component;

class EagerLoadComponents implements ShouldHandleEvents
{
    /**
     * @param Collection<Component> $components
     */
    public function handle(Collection $components)
    {
        $fieldTypes = [Assets::class => [], Entries::class => []];

        // foreach loops are just as optimzed as array_search queries so we'll opt for readability
        // here and just loop over all the components looking for any fields that can be eager loaded.
        //
        // Compare the following two approaches, both taking the same 21-23ms to run
        // https://3v4l.org/PJRaF#v8.2.13
        // https://3v4l.org/ETqcS#v8.2.13
        foreach ($components as $component) {
            foreach ($component->getType()->getFieldDefinitions() as $field) {
                foreach ($fieldTypes as $type => &$ids) {
                    if ($field->className === $type) {
                        $ids = array_merge($ids, $component->data->getRaw($field->handle) ?? []);
                    }
                }
            }
        }

        $fieldTypes[Assets::class] = Asset::find()->id($fieldTypes[Assets::class])->collect()->keyBy('id');
        $fieldTypes[Entries::class] = Entry::find()->id($fieldTypes[Entries::class])->collect()->keyBy('id');

        foreach ($components as $component) {
            foreach ($component->getType()->getFieldDefinitions() as $field) {
                foreach ($fieldTypes as $type => $elements) {
                    if ($field->className === $type) {
                        $componentData = collect($component->data->getRaw($field->handle) ?? [])
                            ->map(fn($id) => $elements->get($id))
                            ->filter();
                        $component->data->populateRelation($field->handle, $componentData);
                    }
                }
            }
        }
    }
}
