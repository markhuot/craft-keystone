<?php

namespace markhuot\keystone\attributes;

use Craft;
use markhuot\keystone\base\Attribute;
use Twig\Markup;

class Text extends Attribute
{
    public function __construct(
        protected ?array $value
    ) {
    }

    public function getInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('keystone/styles/text.twig', [
            ...$this->value,
        ]);
    }

    public function toAttributeArray(): array
    {
        return ['class' => ''];
    }
}
