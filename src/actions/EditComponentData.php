<?php

namespace markhuot\keystone\actions;

use markhuot\keystone\models\Component;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory;

class EditComponentData
{
    public function handle(Component $component, array $data)
    {
        if ($this->isEqual($component->data->getData(), $data)) {
            return $component;
        }

        // Touch the component so we log that the data has updated
        $component->touch();

        // Save the data
        $componentData = $component
            ->maybeDuplicateData()
            ->merge($data)
            ->save();

        return $component;
    }

    /**
     * We're using PHPUnit's array comparator to see if the data has actually changed.
     * This allows us to detect if the arrays are structurally similar even if the order
     * of the keys has changed.
     */
    protected function isEqual(mixed $excepted, mixed $actual): bool
    {
        $factory = new Factory;
        $comparator = $factory->getComparatorFor($excepted, $actual);
        try {
            $comparator->assertEquals($excepted, $actual, delta: 0.0, canonicalize: true);
        } catch (ComparisonFailure $exception) {
            return false;
        }

        return true;
    }
}
