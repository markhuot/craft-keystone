<?php

namespace markhuot\keystone\events;

use craft\base\Event;
use Illuminate\Support\Collection;

class AfterPopulateTree extends Event
{
    public Collection $components;
}
