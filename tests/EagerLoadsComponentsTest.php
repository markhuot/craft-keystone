<?php

use markhuot\keystone\models\Component;

it('eager loads all components for an element/field combo', function () {
    $section = Component::factory()->create(['type' => 'keystone/section']);
    Component::factory()->create(['type' => 'keystone/text']);
})->skip();
