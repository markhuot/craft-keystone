<?php

namespace markhuot\keystone\attributes;

use craft\elements\Asset;
use markhuot\keystone\base\Attribute;

class Background extends Attribute
{
    public function __construct(
        protected ?array $value = []
    ) {
    }

    public function getInputHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate('keystone/styles/background', [
            'label' => 'Background',
            'name' => get_class($this),
            'value' => $this->value ?? null,
        ]);
    }

    public function toAttributeArray(): array
    {
        return ['class' => collect($this->value)
            ->mapKey('color', function ($value) {
                return $value ? 'bg-[#'.$value.']' : null;
            })
            ->mapKey('image', function ($value) {
                if (empty($value)) {
                    return null;
                }

                $asset = Asset::find()->id($value)->one();

                return 'bg-[url('.$asset->getUrl().')]';
            })
            ->mapKey('repeat', function ($value) {
                return $value ? 'bg-repeat' : 'bg-no-repeat';
            })
            ->join(' '),
        ];
    }
}
