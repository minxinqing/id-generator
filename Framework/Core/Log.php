<?php

namespace Framework\Core;

use Framework\Main;
use SeasLog;

class Log
{
    public static function init()
    {
        SeasLog::setBasePath(Main::$rootPath . DS . 'runtime' . DS . 'log');
    }

    public static function __callStatic($name, $arguments)
    {
        forward_static_call_array(['SeasLog', $name], $arguments);

    }
}