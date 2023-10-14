<?php

namespace markhuot\keystone;

use Craft;
use craft\base\Element;
use craft\services\Elements;
use craft\services\Fields;
use craft\web\Application as WebApplication;
use craft\web\Response;
use craft\web\UrlManager;
use craft\web\View;
use Illuminate\Support\Collection;
use markhuot\keystone\base\AttributeBag;
use markhuot\keystone\base\Plugin;
use markhuot\keystone\listeners\AddBodyParamObjectBehavior;
use markhuot\keystone\actions\GetComponentType;
use markhuot\keystone\listeners\AfterPropagateElement;
use markhuot\keystone\listeners\AttachFieldHtmlBehavior;
use markhuot\keystone\listeners\DiscoverSiteComponentTypes;
use markhuot\keystone\listeners\MarkClassesSafeForTwig;
use markhuot\keystone\listeners\OverrideDraftResponseWithFieldHtml;
use markhuot\keystone\listeners\RegisterCollectionMacros;
use markhuot\keystone\listeners\RegisterCpUrlRules;
use markhuot\keystone\listeners\RegisterDefaultComponentTypes;
use markhuot\keystone\listeners\RegisterKeystoneFieldType;
use markhuot\keystone\listeners\RegisterTwigExtensions;
use markhuot\keystone\twig\ExportExtension;
use markhuot\keystone\twig\ExportTokenParser;
use Twig\Extension\EscaperExtension;
use function markhuot\keystone\helpers\listen;

class Keystone extends Plugin
{
    public function init(): void
    {
        listen(
            [WebApplication::class, WebApplication::EVENT_BEFORE_REQUEST, AddBodyParamObjectBehavior::class],
            [Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, RegisterKeystoneFieldType::class],
            [UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, RegisterCpUrlRules::class],
            [GetComponentType::class, GetComponentType::EVENT_REGISTER_COMPONENT_TYPES, RegisterDefaultComponentTypes::class],
            [GetComponentType::class, GetComponentType::EVENT_REGISTER_COMPONENT_TYPES, DiscoverSiteComponentTypes::class],
            [Element::class, Element::EVENT_DEFINE_BEHAVIORS, AttachFieldHtmlBehavior::class],
            [Plugin::class, Plugin::EVENT_INIT, MarkClassesSafeForTwig::class],
            [Plugin::class, Plugin::EVENT_INIT, RegisterTwigExtensions::class],
            [Plugin::class, Plugin::EVENT_INIT, RegisterCollectionMacros::class],
        );

        parent::init();
    }
}
