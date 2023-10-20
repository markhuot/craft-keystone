<?php

namespace markhuot\keystone\base;

use markhuot\keystone\models\Component;

class SlotDefinition
{
    public function __construct(
        protected Component $component,
        protected ?string $slotName,
        protected array $whitelist=[],
        protected array $blacklist=[],
    ) { }

    public function allow(array $types): self
    {
        $this->whitelist = $types;

        return $this;
    }

    public function deny(array $types): self
    {
        $this->blacklist = $types;

        return $this;
    }

    public function getConfig()
    {
        return [
            'name' => $this->slotName,
            'whitelist' => $this->whitelist,
            'blacklist' => $this->blacklist,
        ];
    }

    public function __toString(): string
    {
        return $this->component->getSlot($this->slotName);
    }
}
