<?php

it('filters flat', function () {
    $foo = collect(['a', '__undefined__', 'b'])
        ->filterRecursive(fn ($value) => $value !== '__undefined__')
        ->toArray();

    expect($foo)->toEqualCanonicalizing(['a', 'b']);
});

it('filters recursive', function () {
    $foo = collect(['a', 'foo' => ['bar' => '__undefined__'], 'b'])
        ->filterRecursive(fn ($value) => $value !== '__undefined__')
        ->toArray();

    expect($foo)->toEqualCanonicalizing(['a', 'b', 'foo' => []]);
});
