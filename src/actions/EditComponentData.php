<?php

namespace markhuot\keystone\actions;

use markhuot\keystone\models\Component;

class EditComponentData
{
    public function handle(Component $component, array $data)
    {
        $component->data->merge($data);
        $component->data->save();
        $component->refresh();

        return $component;
    }
}
