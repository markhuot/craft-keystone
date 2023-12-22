<?php

namespace markhuot\keystone\attributes;

use Craft;
use craft\elements\Asset;
use craft\web\View;
use Illuminate\Support\Collection;
use markhuot\keystone\base\Attribute;

class Background extends Attribute
{
    public function __construct(
        protected ?array $value = []
    ) {
    }

    public function getInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('keystone/attributes/background', [
            'label' => 'Background',
            'name' => get_class($this),
            'value' => $this->value ?? null,
        ], View::TEMPLATE_MODE_CP);
    }

    public function getCssRules(): Collection
    {
        return collect($this->value)
            ->forgetWhen(['color', 'image'], fn ($value) => empty($value))
            ->mapWithKeys(fn ($value, $key) => match ($key) {
                'image' => ['background-image' => 'url('.Asset::find()->id($value)->one()?->getUrl().')'],
                'color' => ['background-color' => '#'.$value],
                'repeat' => ['background-repeat' => $value ? 'repeat' : 'no-repeat'],
                default => ['background-'.$key => $value],
            });
    }
}
