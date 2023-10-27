<?php

namespace markhuot\keystone\helpers\base;

use Craft;
use craft\helpers\App;

function app(): \craft\web\Application|\craft\console\Application
{
    return Craft::$app;
}

function parseEnv(string $alias): string
{
    $result = App::parseEnv($alias);

    throw_if(! $result || ! is_string($result) || $result === $alias, 'The alias '.$alias.' could not be resolved');

    return $result;
}

/**
 * @template T
 *
 * @phpstan-assert !true $condition
 *
 * @param  T  $condition
 * @return T
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
 * @template T
 *
 * @phpstan-assert true $condition
 *
 * @param  T  $condition
 * @return T
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
