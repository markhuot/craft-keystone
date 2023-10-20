<?php

namespace markhuot\keystone\twig;

class Exports
{
    public function __construct(
        public $exports=[]
    ) { }

    function add($key, $value) {
        $this->exports[$key] = $value;
    }

    function __get($key)
    {
        return $this->exports[$key] ?? null;
    }
}
