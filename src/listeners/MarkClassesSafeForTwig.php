<?php

namespace markhuot\keystone\listeners;

use Craft;
use craft\base\Event;
use craft\web\View;
use markhuot\keystone\base\AttributeBag;
use markhuot\keystone\base\ComponentType;
use markhuot\keystone\base\SlotDefinition;
use markhuot\keystone\collections\SlotCollection;
use markhuot\keystone\models\Component;
use Twig\Extension\EscaperExtension;

class MarkClassesSafeForTwig
{
    public function handle(Event $event)
    {
        // Bit of a hack but we need to make sure that the AttributeBag is marked as safe in
        // both the CP and the Site mode because we render templates out of both modes during
        // a regular request. This is what allows plugins to ship default components out of
        // their "cp" templates and use them on the front-end "site".
        $oldTemplateMode = Craft::$app->getView()->getTemplateMode();
        foreach ([View::TEMPLATE_MODE_CP, View::TEMPLATE_MODE_SITE] as $mode) {
            Craft::$app->getView()->setTemplateMode($mode);
            $escaper = Craft::$app->getView()->getTwig()->getExtension(EscaperExtension::class);
            $escaper->addSafeClass(AttributeBag::class, ['all']);
            $escaper->addSafeClass(Component::class, ['all']);
            $escaper->addSafeClass(SlotCollection::class, ['all']);
            $escaper->addSafeClass(SlotDefinition::class, ['all']);
        }
        Craft::$app->getView()->setTemplateMode($oldTemplateMode);
    }
}
