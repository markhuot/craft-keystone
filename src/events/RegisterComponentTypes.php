<?php

namespace markhuot\keystone\events;

use craft\base\Event;
use Illuminate\Support\Collection;

class RegisterComponentTypes extends Event
{
    protected Collection $twigTypes;

    protected Collection $classTypes;

    public function __construct($config = [])
    {
        $this->twigTypes = collect();
        $this->classTypes = collect();
    }

    public function registerTwigTemplate($key, $path): void
    {
        $this->twigTypes[$key] = $path;
    }

    public function registerClass($key, $className): void
    {
        $this->classTypes[$key] = $className;
    }

    public function getTwigComponents(): Collection
    {
        return $this->twigTypes;
    }

    public function getClassComponents(): Collection
    {
        return $this->classTypes;
    }
}
