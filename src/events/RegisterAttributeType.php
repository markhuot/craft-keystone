<?php

namespace markhuot\keystone\events;

use craft\base\Event;
use Illuminate\Support\Collection;
use markhuot\keystone\base\Attribute;

class RegisterAttributeType extends Event
{
    protected Collection $types;

    public function __construct($config = [])
    {
        $this->types = collect();
    }

    /**
     * @param  class-string<Attribute>  $attribute
     */
    public function add(string $attribute): self
    {
        $this->types->push($attribute);

        return $this;
    }

    public function getTypes(): Collection
    {
        return $this->types;
    }
}
