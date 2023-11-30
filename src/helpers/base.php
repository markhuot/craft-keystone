<?php

namespace markhuot\keystone\helpers\base;

use Craft;
use craft\helpers\App;
use craft\elements\User;
use yii\web\UnauthorizedHttpException;

function app(): \craft\web\Application|\craft\console\Application
{
    /** @var \craft\web\Application|\craft\console\Application $app */
    $app = Craft::$app;

    return $app;
}

/**
 * @template T
 * @param class-string<T> $className
 * @return T
 */
function resolve(string $className)
{
    /** @var T $instance */
    $instance = Craft::$container->get($className);

    return $instance;
}

function parseEnv(string $alias): string
{
    $result = App::parseEnv($alias);

    throw_if(! $result || ! is_string($result) || $result === $alias, 'The alias '.$alias.' could not be resolved');

    return $result;
}

function currentUserOrFail(): User
{
    $user = app()->getUser()->getIdentity();
    throw_if(! $user, new UnauthorizedHttpException);

    return $user;
}

/**
 * @phpstan-assert !true $condition
 */
function throw_if(mixed $condition, \Exception|string $message): void
{
    if ($condition) {
        if (is_object($message) && $message instanceof \Exception) {
            throw $message;
        } else {
            throw new \RuntimeException($message);
        }
    }
}

/**
 * @phpstan-assert true $condition
 */
function throw_unless(mixed $condition, \Exception|string $message): void
{
    if (! $condition) {
        if (is_object($message) && $message instanceof \Exception) {
            throw $message;
        } else {
            throw new \RuntimeException($message);
        }
    }
}
