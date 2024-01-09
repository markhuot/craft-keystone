<?php

namespace markhuot\keystone\base;

use Illuminate\Support\Collection;
use markhuot\keystone\models\Component;
use Twig\Markup;

class SlotDefinition
{
    protected bool $collapsed = false;

    public function __construct(
        protected ?Component $component = null,
        protected ?string $name = null,
        protected array $whitelist = [],
        protected array $blacklist = [],

        /** @var array{type: string, data?: array<mixed>} $defaults */
        protected array $defaults = [],
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

    /**
     * @param  array{type: string, data?: array<mixed>}  $componentConfig
     */
    public function defaults(array $componentConfig): self
    {
        $this->defaults = $componentConfig;

        return $this;
    }

    public function collapsed(bool $collapsed = true): self
    {
        $this->collapsed = $collapsed;

        return $this;
    }

    public function isCollapsed(): bool
    {
        return $this->collapsed;
    }

    public function allows(string $type): bool
    {
        if (!empty($this->whitelist)) {
            return array_search($type, $this->whitelist) !== false;
        }

        if (!empty($this->blacklist)) {
            return array_search($type, $this->blacklist) === false;
        }

        return true;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function hasWhitelist(): bool
    {
        return count($this->whitelist) > 0;
    }

    public function getWhitelist(): array
    {
        return $this->whitelist;
    }

    public function hasBlacklist(): bool
    {
        return count($this->blacklist) > 0;
    }

    public function getBlacklist(): array
    {
        return $this->blacklist;
    }

    /**
     * @todo, this should be typed to $this->defaults
     *
     * @return Collection<array-key, array<mixed>>
     */
    public function getDefaults(): Collection
    {
        return collect($this->defaults);
    }

    public function getConfig()
    {
        return [
            'name' => $this->name,
            'whitelist' => $this->whitelist,
            'blacklist' => $this->blacklist,
            'defaults' => $this->defaults,
        ];
    }

    public function render(array $context = []): Markup
    {
        return $this->component->getSlot($this->name)->render($context);
    }

    public function __toString(): string
    {
        try {
            return $this->component->getSlot($this->name);
        } catch (\Throwable $e) {
            // throw the previous exception, when present, because this
            // exception is usually just that ->getSlot is not a string
            if ($e->getPrevious()) {
                throw $e->getPrevious();
            } else {
                throw $e;
            }
        }
    }
}
