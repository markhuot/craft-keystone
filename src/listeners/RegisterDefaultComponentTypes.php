<?php

namespace markhuot\keystone\listeners;

use markhuot\keystone\base\ComponentType;
use markhuot\keystone\events\RegisterComponentTypes;

class RegisterDefaultComponentTypes
{
    public function handle(RegisterComponentTypes $event)
    {
//        $event->types[] = new class { protected array $data=[]; function getName() { return 'Section'; } function getType() { return 'keystone/section'; } function load(array $data) { $this->data = $data; return $this; } function render() { return \Craft::$app->getView()->renderTemplate('keystone/components/section.twig', $this->data, \craft\web\View::TEMPLATE_MODE_CP); } };
//        $event->types[] = new class { protected array $data=[]; function getName() { return 'Heading'; } function getType() { return 'keystone/heading'; } function load(array $data) { $this->data = $data; return $this; } function render() { return \Craft::$app->getView()->renderTemplate('keystone/components/heading.twig', $this->data, \craft\web\View::TEMPLATE_MODE_CP); } };
//        $event->types[] = new class { protected array $data=[]; function getName() { return 'Text'; } function getType() { return 'keystone/text'; } function load(array $data) { $this->data = $data; return $this; } function render() { return \Craft::$app->getView()->renderTemplate('keystone/components/text.twig', $this->data, \craft\web\View::TEMPLATE_MODE_CP); } };
//        $event->types[] = new class { protected array $data=[]; function getName() { return 'Fragment'; } function getType() { return 'keystone/fragment'; } function load(array $data) { $this->data = $data; return $this; } function render() { return \Craft::$app->getView()->renderTemplate('keystone/components/fragment.twig', $this->data, \craft\web\View::TEMPLATE_MODE_CP); } };

        $event->types[] = new class extends ComponentType { protected string $type = 'keystone/section'; };
        $event->types[] = new class extends ComponentType { protected string $type = 'keystone/heading'; };
        $event->types[] = new class extends ComponentType { protected string $type = 'keystone/text'; };
        $event->types[] = new class extends ComponentType { protected string $type = 'keystone/fragment'; };
    }
}
