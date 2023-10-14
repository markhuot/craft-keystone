<?php

namespace markhuot\keystone\styles;

use craft\helpers\Cp;
use markhuot\keystone\base\Style;
use Twig\Markup;

class Text extends Style
{
    public function __construct(
        protected ?array $value
    ) { }

    public function getInputHtml(): Markup
    {
        return new Markup(\Craft::$app->getView()->renderTemplate('keystone/styles/text.twig', [
            ...$this->value,
        ]), 'utf-8');
    }

    public function toAttributeArray(): array
    {
        return ['class' => ''];
    }
}
