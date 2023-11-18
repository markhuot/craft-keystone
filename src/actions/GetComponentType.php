<?php

namespace markhuot\keystone\actions;

use Craft;
use craft\base\Event;
use Illuminate\Support\Collection;
use markhuot\keystone\base\ComponentType;
use markhuot\keystone\events\RegisterComponentTypes;
use markhuot\keystone\models\Component;

class GetComponentType
{
    const EVENT_REGISTER_COMPONENT_TYPES = 'registerKeystoneComponentTypes';

    protected static ?RegisterComponentTypes $_types = null;

    public function __construct(
        protected ?Component $context = null
    ) {
    }

    protected function getTypes()
    {
        if (static::$_types !== null) {
            return static::$_types;
        }

        $event = new RegisterComponentTypes;
        Event::trigger(static::class, static::EVENT_REGISTER_COMPONENT_TYPES, $event);

        return static::$_types = $event;
    }

    /**
     * @return Collection<ComponentType>
     */
    public function all(): Collection
    {
        return $this->getTypes()->getTwigComponents()
            ->mapInto(CompileTwigComponent::class)->map->handle()
            ->merge($this->getTypes()->getClassComponents())
            ->map(fn ($className) => new $className($this->context));
    }

    public function byType(string $type): ComponentType
    {
        if ($twigPath = $this->getTypes()->getTwigComponents()->get($type)) {
            $fqcn = (new CompileTwigComponent($twigPath, $type))->handle();
        }

        if ($className = $this->getTypes()->getClassComponents()->get($type)) {
            $fqcn = $className;
        }

        if ($fqcn) {
            return Craft::$container->get($fqcn, ['context' => $this->context]);
        }

        throw new \RuntimeException('Could not find a component type definition for '.$type);
    }
}
