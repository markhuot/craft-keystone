<?php

namespace markhuot\keystone\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\db\Query;
use craft\elements\conditions\entries\EntryCondition;
use craft\elements\db\EntryQuery;
use craft\elements\Entry;

class Condition extends Field implements FieldInterface
{
    protected function inputHtml(mixed $value, ElementInterface $element = null): string
    {
        if (empty($value)) {
            $value = ['class' => EntryCondition::class];
        }
        if ($value instanceof Query) {
            $value = $value->getCondition();
        }

        $condition = Craft::$app->getConditions()->createCondition($value);
        $condition->mainTag = 'div';
        $condition->id = $this->handle;
        $condition->name = $this->handle;

        return \Craft::$app->getView()->renderTemplate('keystone/fields/condition', [
            'condition' => $condition,
            'value' => $value,
        ]);
    }

    public function normalizeValue(mixed $value, ElementInterface $element = null): mixed
    {
        $condition = Craft::$app->getConditions()->createCondition($value);

        $condition->modifyQuery($query = new EntryQuery(Entry::class));
        $query->setCondition($value);

        return $query;
    }
}
