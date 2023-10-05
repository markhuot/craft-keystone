<?php

namespace markhuot\keystone\base;

use Craft;
use craft\elements\Entry;
use craft\fields\PlainText;
use markhuot\keystone\models\Component;
use Twig\Markup;

class ComponentType
{
    protected string $type;

    public function getName(): string
    {
        [, $name] = explode('/', $this->type);

        return ucfirst($name);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getIcon(array $attributes = []): Markup
    {
        $attributes = collect($attributes)
            ->map(fn ($v, $k) => "{$k}=\"{$v}\"")
            ->join(' ');

        // https://phosphoricons.com
        return new Markup('<svg ' . $attributes .  ' xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><path d="M165.78,224H208a16,16,0,0,0,16-16V170.35A8,8,0,0,0,212.94,163a23.37,23.37,0,0,1-8.94,1.77c-13.23,0-24-11.1-24-24.73s10.77-24.73,24-24.73a23.37,23.37,0,0,1,8.94,1.77A8,8,0,0,0,224,109.65V72a16,16,0,0,0-16-16H171.78a35.36,35.36,0,0,0,.22-4,36,36,0,0,0-72,0,35.36,35.36,0,0,0,.22,4H64A16,16,0,0,0,48,72v32.22a35.36,35.36,0,0,0-4-.22,36,36,0,0,0,0,72,35.36,35.36,0,0,0,4-.22V208a16,16,0,0,0,16,16h42.22"/></svg>', 'utf-8');

        // return new Markup('<svg ' . $attributes . ' xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><path d="M208,32H48A16,16,0,0,0,32,48V208a16,16,0,0,0,16,16H208a16,16,0,0,0,16-16V48A16,16,0,0,0,208,32ZM184,96a8,8,0,0,1-16,0V88H136v88h16a8,8,0,0,1,0,16H104a8,8,0,0,1,0-16h16V88H88v8a8,8,0,0,1-16,0V80a8,8,0,0,1,8-8h96a8,8,0,0,1,8,8Z"/></svg>', 'utf-8');
        // return new Markup('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><path d="M64,216a8,8,0,0,1-8-8V165.31a28,28,0,1,1,0-50.62V72a8,8,0,0,1,8-8h46.69a28,28,0,1,1,50.61,0H208a8,8,0,0,1,8,8v42.69a28,28,0,1,0,0,50.62V208a8,8,0,0,1-8,8Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="8"/></svg>', 'utf-8');
        // return '🧩';
    }

    public function render(array $variables=[]): string
    {
        return Craft::$app->getView()->renderTemplate(
            template: $this->getTemplatePath(),
            variables: $variables,
            templateMode: \craft\web\View::TEMPLATE_MODE_CP,
        );
    }

    public function getTemplatePath(): string
    {
        [$namespace, $name] = explode('/', $this->type);

        return $namespace . '/components/' . $name . '.twig';
    }

    public function getFields(): array
    {
        $component = new Component();
        $component->type = $this->type;
        $this->render(['component' => $component, 'element' => new Entry]);

        return collect($component->data->getAccessed())
            ->map(fn ($config) => $config->build())
            ->toArray();
    }
}
