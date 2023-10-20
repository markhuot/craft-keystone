<?php

namespace markhuot\keystone\styles;

use markhuot\keystone\base\Attribute;
use Twig\Markup;

class Text extends Attribute
{
    public function __construct(
        protected ?array $value
    ) {
    }

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
