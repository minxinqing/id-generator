<?php

namespace Framework\Coroutine;

use Swoole\Coroutine as SwCo;
use Swoole\Coroutine\Channel as Channel;

class Coroutine
{
    /**
     * @var array
     * @desc 保存当前协程根Id
     *      结构：["当前协程ID" => "根协程ID"]
     */
    public static $idMaps = [];

    /**
     * @return mixed
     * @desc 获取当前协程Id
     */
    public static function getId()
    {
        return SwCo::getuid();
    }

    /**
     * @return mixed
     * @desc 父Id自设，onRequest回调后的第一个协程，把根协程ID设置为自己
     */
    public static function setBaseId()
    {
        $id = self::getId();
        self::$idMaps[$id] = $id;
        return $id;
    }

    /**
     * @param null $id
     * @param int $cur
     * @return int|mixed|null
     * @desc 获取当前协程的根协程ID
     */
    public static function getPid($id = null, $cur = 1)
    {
        if ($id === null) {
            $id = self::getId();
        }

        if (isset(self::$idMaps[$id])) {
            return self::$idMaps[$id];
        }

        return $cur ? $id : -1;
    }

    /**
     * @return bool
     * @desc 判断是否根协程
     */
    public static function checkBaseCo()
    {
        $id = self::getId();
        if (empty(self::$idMaps[$id])) {
            return false;
        }

        if ($id !== self::$idMaps[$id]) {
            return false;
        }

        return true;
    }

    /**
     * 创建协程通道
     *
     * @param $len
     * @return SwCo\Channel
     */
    public static function createChannel($len)
    {
        $channel = new Channel($len);
        return $channel;
    }

    /**
     * 获取通道中所有结果
     *
     * @param Channel $channel
     * @return array|mixed
     */
    public static function getCoResult(Channel $channel, $len)
    {
        $result = [];
        for ($i = 0; $i < $len; $i++) {
            $result += $channel->pop();
        }
        $channel->close();
        return $result;
    }

    /**
     * 创建协程
     *
     * @param $cb
     * @param null $deferCb
     * @return mixed
     */
    public static function create($cb, $deferCb = null)
    {
        $nid = self::getId();
        return go(function () use ($cb, $deferCb, $nid) {
            $id = SwCo::getuid();
            defer(function () use ($deferCb, $id) {
                self::call($deferCb);
                self::clear($id);
            });

            $pid = self::getPid($nid);
            if ($pid == -1) {
                $pid = $nid;
            }
            self::$idMaps[$id] = $pid;
            self::call($cb);
        });
    }

    public static function call($cb, $args = [])
    {
        if (empty($cb)) {
            return null;
        }

        $ret = null;
        if (\is_callable($cb) || (\is_string($cb) && \function_exists($cb))) {
            $ret = $cb(...$args);
        } elseif (\is_array($cb)) {
            list($obj, $mhd) = $cb;
            $ret = \is_object($obj) ? $obj->$mhd(...$args) : $obj::$mhd(...$args);
        }
        return $ret;
    }

    /**
     * @param null $id
     * @desc 协程退出，清除关系树
     */
    public static function clear($id = null)
    {
        if (null === $id) {
            $id = self::getId();
        }
        unset(self::$idMaps[$id]);
    }
}