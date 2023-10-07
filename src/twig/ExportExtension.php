<?php

namespace markhuot\keystone\twig;

use markhuot\keystone\base\FieldDefinition;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ExportExtension extends AbstractExtension
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
}
