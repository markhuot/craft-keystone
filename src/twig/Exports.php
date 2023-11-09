<?php

namespace markhuot\keystone\twig;

class Exports
{
    /**
     * @param  array<mixed>  $exports
     */
    public function __construct(
        public array $exports = []
    ) {
    }

    public function add(string $key, mixed $value): self
    {
        $this->exports[$key] = $value;

        return $this;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->exports[$key] ?? $default;

        if (is_callable($value)) {
            $value = $value();
        }

        return $value;
    }

    public function __get(string $key): mixed
    {
        return $this->get($key);
    }
}
