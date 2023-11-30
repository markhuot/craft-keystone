<?php

namespace markhuot\keystone;

use craft\base\Element;
use craft\db\Query;
use craft\fields\PlainText;
use craft\services\Fields;
use craft\web\Application as WebApplication;
use craft\web\UrlManager;
use markhuot\keystone\actions\EagerLoadComponents;
use markhuot\keystone\actions\GetAttributeTypes;
use markhuot\keystone\actions\GetComponentType;
use markhuot\keystone\base\Plugin;
use markhuot\keystone\listeners\AttachElementBehaviors;
use markhuot\keystone\listeners\AttachFieldBehavior;
use markhuot\keystone\listeners\AttachPerRequestBehaviors;
use markhuot\keystone\listeners\AttachQueryBehaviors;
use markhuot\keystone\listeners\DiscoverSiteComponentTypes;
use markhuot\keystone\listeners\MarkClassesSafeForTwig;
use markhuot\keystone\listeners\RegisterCollectionMacros;
use markhuot\keystone\listeners\RegisterCpUrlRules;
use markhuot\keystone\listeners\RegisterDefaultAttributeTypes;
use markhuot\keystone\listeners\RegisterDefaultComponentTypes;
use markhuot\keystone\listeners\RegisterKeystoneFieldType;
use markhuot\keystone\listeners\RegisterTwigExtensions;
use markhuot\keystone\models\Component;

class Keystone extends Plugin
{
    protected function getListeners(): array
    {
        return [
            [WebApplication::class, WebApplication::EVENT_BEFORE_REQUEST, AttachPerRequestBehaviors::class],
            [Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, RegisterKeystoneFieldType::class],
            [UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, RegisterCpUrlRules::class],
            [GetComponentType::class, GetComponentType::EVENT_REGISTER_COMPONENT_TYPES, RegisterDefaultComponentTypes::class],
            [GetComponentType::class, GetComponentType::EVENT_REGISTER_COMPONENT_TYPES, DiscoverSiteComponentTypes::class],
            [GetAttributeTypes::class, GetAttributeTypes::EVENT_REGISTER_ATTRIBUTE_TYPE, RegisterDefaultAttributeTypes::class],
            [Element::class, Element::EVENT_DEFINE_BEHAVIORS, AttachElementBehaviors::class],
            [PlainText::class, PlainText::EVENT_DEFINE_BEHAVIORS, AttachFieldBehavior::class],
            [Query::class, Query::EVENT_DEFINE_BEHAVIORS, AttachQueryBehaviors::class],
            [Component::class, Component::AFTER_POPULATE_TREE, EagerLoadComponents::class],
            [Plugin::class, Plugin::EVENT_INIT, MarkClassesSafeForTwig::class],
            [Plugin::class, Plugin::EVENT_INIT, RegisterTwigExtensions::class],
            [Plugin::class, Plugin::EVENT_INIT, RegisterCollectionMacros::class],
        ];
    }
}
