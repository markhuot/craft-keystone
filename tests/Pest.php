<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "markhuot\craftpest\test\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

use markhuot\keystone\behaviors\CssRuleBehavior;

uses(
    markhuot\craftpest\test\TestCase::class,
    markhuot\craftpest\test\RefreshesDatabase::class,
)->in('./');

uses()->beforeEach(function () {
    Craft::setAlias('@templates', __DIR__.'/templates');
    Craft::$app->getView()->attachBehaviors(['cssRules' => CssRuleBehavior::class]);
})->in('./');

uses()->afterEach(function () {
    Craft::$app->getView()->clearCssRules();
})->in('./');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
