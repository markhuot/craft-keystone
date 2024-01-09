<?php

namespace markhuot\keystone\twig;

use markhuot\keystone\base\FieldDefinition;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class KeystoneExtension extends AbstractExtension
{
    public function getTokenParsers()
    {
        return [
            new ExportTokenParser(),
            new SlotTokenParser(),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('field', fn (string $type) => FieldDefinition::for($type)),
            new TwigFunction('config', $this->config(...)),
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('class_exists', 'class_exists'),
            new TwigFilter('is_iterable', 'is_iterable'),
        ];
    }

    protected function config(string $key)
    {
        $segments = explode($key, '.');

        $user = \Craft::$app->getConfig()->getConfigFromFile('keystone');
        $value = data_get($user, $key, '__UNSET__');
        if ($value !== '__UNSET__') {
            return $value;
        }

        $default = require __DIR__ . '/../config/keystone.php';
        $value = data_get($default, $key, '__UNSET__');
        if ($value !== '__UNSET__') {
            return $value;
        }

        return null;
    }
}
