<?php

namespace markhuot\keystone\collections;

use Craft;
use craft\web\View;
use Illuminate\Support\Collection;
use markhuot\keystone\models\Component;
use Twig\Markup;

class SlotCollection extends Collection
{
    protected Component $parent;
    protected ?string $slotName;

    public function __construct(Component $parent, ?string $slotName=null, $items = [])
    {
        $this->parent = $parent;
        $this->slotName = $slotName;

        parent::__construct($items);
    }

    public function toHtml(): Markup
    {
        return new Markup(Craft::$app->getView()->renderTemplate('keystone/_slot', [
            'component' => $this->parent,
            'components' => $this,
        ], View::TEMPLATE_MODE_CP), 'utf-8');
    }
}
