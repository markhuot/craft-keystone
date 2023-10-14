<?php

namespace markhuot\keystone\actions;

use craft\base\Event;
use Illuminate\Support\Collection;
use markhuot\keystone\base\ComponentType;
use markhuot\keystone\events\RegisterComponentTypes;

class GetComponentType
{
    const EVENT_REGISTER_COMPONENT_TYPES = 'registerKeystoneComponentTypes';

    /**
     * @return Collection<ComponentType>
     */
    public function all(): Collection
    {
        $event = new RegisterComponentTypes;
        Event::trigger(static::class, static::EVENT_REGISTER_COMPONENT_TYPES, $event);

        return $event->getTwigComponents()
            ->mapInto(CompileTwigComponent::class)->map->handle()
            ->merge($event->getClassComponents())
            ->map(fn ($className) => new $className);
    }

    public function byType(string $type): ComponentType
    {
        $event = new RegisterComponentTypes;
        Event::trigger(static::class, static::EVENT_REGISTER_COMPONENT_TYPES, $event);

        if ($twigPath = $event->getTwigComponents()->get($type)) {
            return new ((new CompileTwigComponent($twigPath, $type))->handle());
        }

        if ($className = $event->getClassComponents()->get($type)) {
            return new $className;
        }

        throw new \RuntimeException('Could not find a component type definition for '.$type);
    }
}
