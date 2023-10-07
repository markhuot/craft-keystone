<?php

namespace markhuot\keystone\base;

use Craft;
use craft\base\FieldInterface;
use craft\fields\Assets;
use craft\fields\Dropdown;
use craft\fields\Lightswitch;
use craft\fields\PlainText;

class FieldDefinition
{
    public function __construct(
        public array $config=['className' => PlainText::class],
    ) { }

    public static function for(string $name): self
    {
        return new static(['className' => match ($name) {
            'dropdown' => Dropdown::class,
            'plaintext' => PlainText::class,
            'lightswitch' => Lightswitch::class,
            'asset' => Assets::class,
            default => $name,
        }]);
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

        return Craft::$container->get($className, $params, $config);
    }
}
