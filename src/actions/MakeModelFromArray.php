<?php

namespace markhuot\keystone\actions;

use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\base\Model;
use markhuot\keystone\db\ActiveRecord;
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
    public function handle(string $className, mixed $data, $validate = true, $errorOnMissing = false, $createOnMissing = true): mixed
    {
        if (is_subclass_of($className, ActiveRecord::class)) {
            $primaryKey = $className::primaryKey();
            if (! is_array($primaryKey)) {
                $primaryKey = [$primaryKey];
            }
            $condition = array_flip($primaryKey);
            foreach ($condition as $key => &$value) {
                if (is_array($data)) {
                    $value = $data[$key];
                }
                // if (count($condition) === 1) {
                //     $value = $data;
                // }
            }
            $condition = array_filter($condition);

            if (count($condition)) {
                $model = $className::findOne($condition);
            }
        } elseif ($className === ElementInterface::class) {
            $model = app()->getElements()->getElementById($data);
        } elseif ($className === FieldInterface::class) {
            $model = app()->getFields()->getFieldById($data);
        }

        if (empty($model) && $createOnMissing) {
            $model = new $className;
        }

        if (empty($model) && $errorOnMissing) {
            throw new \RuntimeException('Could not find a matching '.$className);
        }

        if (empty($model)) {
            return null;
        }

        $this->load($model, $data);

        if ($validate) {
            $this->validate($model);
        }

        return $model;
    }

    protected function load(\yii\base\Model $model, mixed $data): void
    {
        if (! is_array($data)) {
            return;
        }

        $reflect = new \ReflectionClass($model);

        foreach ($data as $key => &$value) {
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
        }

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
