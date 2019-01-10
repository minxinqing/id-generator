<?php

namespace Framework\Pool;

use Framework\Coroutine\Coroutine;


class Context
{
    public static $pool = [];

    /**
     * @return \Framework\Coroutine\Context|null
     */
    public static function getContext()
    {
        $id = Coroutine::getPid();

        return self::$pool[$id] ?? null;

    }

    public static function clear()
    {
        $id = Coroutine::getPid();
        if (isset(self::$pool[$id])) {
            unset(self::$pool[$id]);
            Coroutine::clear($id);
        }
    }

    public static function set($context)
    {
        $id = Coroutine::getPid();
        self::$pool[$id] = $context;
    }
}