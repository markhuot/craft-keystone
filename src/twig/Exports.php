<?php

namespace markhuot\keystone\twig;

class Exports
{
    public function __construct(
        public $exports = []
    ) {
    }

    public function add($key, $value)
    {
        $this->exports[$key] = $value;
    }

    public function __get($key)
    {
        return $this->exports[$key] ?? null;
    }
}
