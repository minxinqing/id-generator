<?php

namespace Framework\Core;

use Framework\Main;

class Config
{
    public static $configMap = [];

    public static function load($configPath = false)
    {
        $configPath || $configPath = Main::$rootPath . DS . 'Config';
        $dirArr = scandir($configPath);

        foreach ($dirArr as $v) {
            if ($v == '.' || $v == '..') {
                continue;
            }

            $file = $configPath . DS . $v;
            if (is_dir($file)) {
                self::load($v);
            } else {
                $config = require $file;
                self::$configMap = array_merge(self::$configMap, $config);
            }
        }

    }

    public static function get($key)
    {
        return self::$configMap[$key] ?? null;
    }
}