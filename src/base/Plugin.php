<?php

namespace markhuot\keystone\base;

use yii\base\Event;

use function markhuot\keystone\helpers\event\listen;

class Plugin extends \craft\base\Plugin
{
    const EVENT_INIT = 'init';

    public function init()
    {
        parent::init();

        $this->setAliases(['@keystone' => __DIR__.'/../']);

        listen(...$this->getListeners());

        $event = new \craft\base\Event();
        $event->sender = $this;
        Event::trigger(self::class, self::EVENT_INIT, $event);
    }

    /**
     * @return array<array{0: class-string, 1: string, 2: class-string}>>
     */
    protected function getListeners(): array
    {
        return [];
    }
}
