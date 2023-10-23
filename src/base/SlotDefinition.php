<?php

namespace markhuot\keystone\base;

use markhuot\keystone\models\Component;

class SlotDefinition
{
    public function __construct(
        protected ?Component $component = null,
        protected ?string $name = null,
        protected array $whitelist = [],
        protected array $blacklist = [],
    ) {
    }

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

    public function allows(string $type): bool
    {
        if (! empty($this->whitelist)) {
            return array_search($type, $this->whitelist) !== false;
        }

        if (! empty($this->blacklist)) {
            return array_search($type, $this->blacklist) === false;
        }

        return true;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getWhitelist(): array
    {
        return $this->whitelist;
    }

    public function getBlacklist(): array
    {
        return $this->blacklist;
    }

    public function getConfig()
    {
        return [
            'name' => $this->name,
            'whitelist' => $this->whitelist,
            'blacklist' => $this->blacklist,
        ];
    }

    public function __toString(): string
    {
        return $this->component->getSlot($this->name);
    }
}
