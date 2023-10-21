<?php

namespace markhuot\keystone\actions;

class GetFileMTime
{
    public static $mocks = [];

    public function handle(string $filepath)
    {
        return static::$mocks[$filepath] ?? @filemtime($filepath) ?? 0;
    }

    public static function mock(string $filepath, int $time)
    {
        static::$mocks[$filepath] = $time;
    }
}
