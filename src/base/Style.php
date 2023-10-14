<?php

namespace markhuot\keystone\base;

use Twig\Markup;

abstract class Style
{
    abstract public function getInputHtml(): Markup;

    abstract public function toAttributeArray(): array;
}
