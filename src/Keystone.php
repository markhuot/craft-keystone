<?php

namespace markhuot\keystone;

use Craft;
use craft\base\Plugin;
use craft\services\Fields;
use craft\web\Application as WebApplication;
use craft\web\UrlManager;
use markhuot\keystone\listeners\AddBodyParamObjectBehavior;
use markhuot\keystone\actions\GetComponentTypes;
use markhuot\keystone\listeners\RegisterCpUrlRules;
use markhuot\keystone\listeners\RegisterDefaultComponentTypes;
use markhuot\keystone\listeners\RegisterKeystoneFieldType;
use function markhuot\keystone\helpers\listen;

class Keystone extends Plugin
{
    public function init()
    {
        parent::init();

        $this->setAliases(['@keystone' => __DIR__]);

        listen(
            [WebApplication::class, WebApplication::EVENT_BEFORE_REQUEST, AddBodyParamObjectBehavior::class],
            [Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, RegisterKeystoneFieldType::class],
            [UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, RegisterCpUrlRules::class],
            [GetComponentTypes::class, GetComponentTypes::EVENT_REGISTER_COMPONENT_TYPES, RegisterDefaultComponentTypes::class],
        );
    }
}
