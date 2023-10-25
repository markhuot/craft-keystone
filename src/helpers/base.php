<?php

namespace markhuot\keystone\helpers\base;

use Craft;

function app(): \craft\web\Application|\craft\console\Application
{
    return Craft::$app;
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
