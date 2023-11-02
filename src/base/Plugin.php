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

    protected function getListeners(): array
    {
        return [];
    }
}
