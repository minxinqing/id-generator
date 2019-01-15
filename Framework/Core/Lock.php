<?php

namespace Framework\Core;

class Lock{

    public static $lock;

    public static function init($num = 16)
    {
        // 声明锁，备用
        $lock = [];
        for ($i = 0; $i <= $num; $i++) {
            $lock[$i] = new \Swoole\Lock(SWOOLE_MUTEX);
        }
        self::$lock = $lock;
    }

    /**
     * @param $i
     * @return \Swoole\Lock
     */
    public static function get($i)
    {
        return self::$lock[$i] ?? null;
    }
}