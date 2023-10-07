<?php

namespace markhuot\keystone\actions;

class GetFileMTime
{
    static $mocks = [];

    public function handle(string $filepath)
    {
        return static::$mocks[$filepath] ?? filemtime($filepath);
    }

    public static function mock(string $filepath, int $time)
    {
        static::$mocks[$filepath] = $time;
    }
}
