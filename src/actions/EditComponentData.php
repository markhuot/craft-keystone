<?php

namespace markhuot\keystone\actions;

use markhuot\keystone\models\Component;

class EditComponentData
{
    public function handle(Component $component, array $data)
    {
        // if the data is unchanged, don't do anything
        // man, php is nice that it can do this out of the box and canonicalizes the
        // array for us too checking the key/values even if they're in a different
        // order
        if ($component->data->data === $data) {
            return $component;
        }

        $component
            ->maybeDuplicateData()
            ->merge($data)
            ->save();

        return $component;
    }
}
