<?php

namespace markhuot\keystone;

use Craft;
use craft\base\Plugin;
use craft\services\Elements;
use craft\services\Fields;
use craft\web\Application as WebApplication;
use craft\web\Response;
use craft\web\UrlManager;
use markhuot\keystone\listeners\AddBodyParamObjectBehavior;
use markhuot\keystone\actions\GetComponentType;
use markhuot\keystone\listeners\AfterPropagateElement;
use markhuot\keystone\listeners\OverrideDraftResponseWithFieldHtml;
use markhuot\keystone\listeners\RegisterCpUrlRules;
use markhuot\keystone\listeners\RegisterDefaultComponentTypes;
use markhuot\keystone\listeners\RegisterKeystoneFieldType;
use markhuot\keystone\twig\ExportExtension;
use markhuot\keystone\twig\ExportTokenParser;
use function markhuot\keystone\helpers\listen;

class Keystone extends Plugin
{
    public function init(): void
    {
        parent::init();

        $this->setAliases(['@keystone' => __DIR__]);
        Craft::$app->getView()->registerTwigExtension(new ExportExtension);

        listen(
            [WebApplication::class, WebApplication::EVENT_BEFORE_REQUEST, AddBodyParamObjectBehavior::class],
            [Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, RegisterKeystoneFieldType::class],
            [UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, RegisterCpUrlRules::class],
            [GetComponentType::class, GetComponentType::EVENT_REGISTER_COMPONENT_TYPES, RegisterDefaultComponentTypes::class],
            [Response::class, Response::EVENT_AFTER_PREPARE, OverrideDraftResponseWithFieldHtml::class],
        );
    }
}
