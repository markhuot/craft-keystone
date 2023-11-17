<?php

use markhuot\keystone\models\Component;

it('supports multiple slots', function () {
    $tabsContainer = Component::factory()->type('keystone/tabs')->create();
    $tab = Component::factory()->type('keystone/tab')->path([$tabsContainer->id])->create();
    $label = Component::factory()->type('keystone/text')->path([$tabsContainer->id, $tab->id])->slot('label')->create();
    $label->data->merge(['text' => 'label'])->save();
    $content = Component::factory()->type('keystone/text')->path([$tabsContainer->id, $tab->id])->create();
    $content->data->merge(['text' => 'content'])->save();

    $tab->refresh();
    expect($tab)
        ->getSlot('label')->toHaveCount(1)
        ->getSlot()->toHaveCount(1);
});
