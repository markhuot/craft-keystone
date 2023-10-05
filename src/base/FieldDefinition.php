<?php

namespace markhuot\keystone\base;

use Craft;
use craft\base\FieldInterface;
use craft\fields\Dropdown;
use craft\fields\PlainText;

class FieldDefinition
{
    public function __construct(
        protected string $handle,
        public array $config=['className' => PlainText::class],
    ) { }

    public function type(string $name): self
    {
        $this->config['className'] = match ($name) {
            'dropdown' => Dropdown::class,
            default => $name,
        };

        return $this;
    }

    public function __call($method, $args): self
    {
        $this->config[$method] = $args[0];

        return $this;
    }

    public function build(): FieldInterface
    {
        $className = $this->config['className'];
        $params = [];
        $config = collect($this->config)->except(['className'])->toArray();
        $config = [
            'name' => ucfirst($this->handle),
            'handle' => $this->handle,
            ...$config,
        ];

        return Craft::$container->get($className, $params, $config);
    }
}
