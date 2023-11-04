<?php

namespace markhuot\keystone\actions;

use craft\base\Event;
use Illuminate\Support\Collection;
use markhuot\keystone\base\ComponentType;
use markhuot\keystone\events\RegisterComponentTypes;
use markhuot\keystone\models\Component;

class GetComponentType
{
    const EVENT_REGISTER_COMPONENT_TYPES = 'registerKeystoneComponentTypes';

    public function __construct(
        protected ?Component $context = null
    ) {
    }

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
            ->map(fn ($className) => new $className($this->context));
    }

    public function byType(string $type): ComponentType
    {
        $event = new RegisterComponentTypes;
        Event::trigger(static::class, static::EVENT_REGISTER_COMPONENT_TYPES, $event);

        if ($twigPath = $event->getTwigComponents()->get($type)) {
            return new ((new CompileTwigComponent($twigPath, $type))->handle())($this->context);
        }

        if ($className = $event->getClassComponents()->get($type)) {
            return new $className($this->context);
        }

        throw new \RuntimeException('Could not find a component type definition for '.$type);
    }
}
