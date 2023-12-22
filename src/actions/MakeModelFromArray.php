<?php

namespace markhuot\keystone\actions;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\base\Model;
use markhuot\keystone\db\ActiveRecord;
use markhuot\keystone\exceptions\RecordNotFound;
use yii\base\ModelEvent;

use function markhuot\keystone\helpers\base\app;

/**
 * Recursively create models from an array.
 *
 * ```php
 * (new MakeModelFromArray)->handle(Component::class, ['id' => 123], errorOnMissing: true, createOnMissing: false)
 * ```
 *
 * That would search for component 123 and error out if it could not be found.
 *
 * ```php
 * (new MakeModelFromArray)->handle(PostData::class, ['foo' => 'bar'], errorOnMissing: false, createOnMissing: true)
 * ```
 *
 * That would instantiate a new PostData class with the `foo` property set to `bar`.
 */
class MakeModelFromArray
{
    /**
     * @template T
     *
     * @param  class-string<T>  $className
     * @return T
     */
    public function handle(string $className, mixed $data, $validate = true, $errorOnMissing = true, $createOnMissing = true): mixed
    {
        $model = match (true) {
            is_subclass_of($className, ActiveRecord::class) => $this->getRecordByPrimaryKey($className, $data),
            $className === ElementInterface::class => app()->getElements()->getElementById($data),
            $className === FieldInterface::class => app()->getFields()->getFieldById($data),
            default => new $className,
        };

        if ($errorOnMissing && empty($model)) {
            throw new RecordNotFound('Record not found');
        }

        if ($model) {
            $this->load($model, $data);

            if ($validate) {
                $this->validate($model);
            }
        }

        return $model;
    }

    protected function getRecordByPrimaryKey(string $className, mixed $data): ?ActiveRecord
    {
        $primaryKeyFields = $className::primaryKey();

        // If primaryKey is a single field and a single value was passed in to $data assume
        // the primaryKey field name
        if (count($primaryKeyFields) === 1 && (is_string($data) || is_numeric($data))) {
            $data = [$primaryKeyFields[0] => $data];
        }

        // check to make sure that either all the primary key fields are present in the
        // data array or _none_ of the primary key fields are present
        $searchedKeyFields = collect($data)->keys()->intersect($primaryKeyFields);
        if (count($searchedKeyFields) !== 0 && count($searchedKeyFields) !== count($primaryKeyFields)) {
            throw new \RuntimeException('Missing primary key fields');
        }

        // If all the primary key fields are present in the data array then use them to
        // search for a model
        if (count($searchedKeyFields) > 0) {
            $condition = collect($data)->only($searchedKeyFields)->toArray();
            $model = $className::findOne($condition);
        }
        else {
            $model = new $className;
        }

        return $model;
    }

    protected function load(\yii\base\Model $model, mixed $data): void
    {
        if (! is_array($data)) {
            return;
        }

        $reflect = new \ReflectionClass($model);

        foreach ($data as $key => $value) {
            // If we were passed something like elementId over the wire, but want to deal with
            // a property of `->element` then check for that here and swap the keys out.
            if (str_ends_with($key, 'Id') && ! $reflect->hasProperty($key)) {
                unset($data[$key]);
                $key = substr($key, 0, -2);
            }

            if ($reflect->hasProperty($key)) {
                $property = $reflect->getProperty($key);
                $type = $property->getType()->getName();

                if (enum_exists($type)) {
                    $value = $type::from($value);
                } elseif (class_exists($type) || interface_exists($type)) {
                    $value = (new static)
                        ->handle(
                            className: $type,
                            data: $value,
                            validate: true,
                            errorOnMissing: false,
                            createOnMissing: false,
                        );
                }
            }

            $data[$key] = $value;
        }

        // We can't set a non-null property to null, or it would error out here during the load
        // phase instead of during the validation phase. So, if a value is null but the model doesn't
        // support that remove the null value from the data array. This will leave the value unset
        // in the model and it will later fail validation.
        //
        // Basically, we're punting null errors further down in the processing so it can catch during
        // validation and not throw a runtime error here during load.
        $reflect = new \ReflectionClass($model);
        foreach ($reflect->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (! $property->getType()?->allowsNull() && ($data[$property->getName()] ?? null) === null) {
                $model->addError($property->getName(), $property->getName().' can not be null');
                unset($data[$property->getName()]);
            }
        }

        $model->load($data, '');
    }

    protected function validate(\yii\base\Model $model): void
    {
        $catch = function (ModelEvent $event) {
            $reflect = new \ReflectionClass($event->sender);
            foreach ($reflect->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                if (! $property->isInitialized($event->sender) && ! $property->getType()?->allowsNull()) {
                    $event->sender->addError($property->getName(), $property->getName().' is required');
                }
            }
        };

        $model->on(Model::EVENT_BEFORE_VALIDATE, $catch);

        $model->validate();

        $model->off(Model::EVENT_BEFORE_VALIDATE, $catch);
    }
}
