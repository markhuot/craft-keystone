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
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('class_exists', 'class_exists'),
            new TwigFilter('is_iterable', 'is_iterable'),
        ];
    }
}
