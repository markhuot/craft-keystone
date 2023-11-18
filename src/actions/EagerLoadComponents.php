<?php

namespace markhuot\keystone\actions;

use craft\elements\Asset;
use craft\fields\Assets;
use Illuminate\Support\Collection;
use markhuot\keystone\interfaces\ShouldHandleEvents;

class EagerLoadComponents implements ShouldHandleEvents
{
    public function handle(Collection $components)
    {
        $assetIds = [];

        foreach ($components as $component) {
            foreach ($component->getType()->getFieldDefinitions() as $field) {
                if ($field->className === Assets::class) {
                    $assetIds = array_merge($assetIds, $component->data->getRaw($field->handle) ?? []);
                }
            }
        }

        $assets = Asset::find()->id($assetIds)->collect()->keyBy('id');

        foreach ($components as $component) {
            foreach ($component->getType()->getFieldDefinitions() as $field) {
                if ($field->className === Assets::class) {
                    $componentData = collect($component->data->getRaw($field->handle) ?? [])
                        ->map(fn ($id) => $assets->get($id))
                        ->filter();
                    $component->data->populateRelation($field->handle, $componentData);
                }
            }
        }
    }
}
