<?php

namespace App\Controller;

use Framework\MVC\Controller;
use Framework\Coroutine\Coroutine;
use App\Service\User as UserService;
use App\Service\Snowflake as SnowflakeService;

class Index extends Controller
{
    public function index()
    {
        return 'i am index:';
    }

    public function coroutine()
    {
        $s = microtime(true);

        // 创建通道，用于协程返回值
        $channel = Coroutine::createChannel(2);

        // 创建协程1
        Coroutine::create(function () use ($channel) {
            \Swoole\Coroutine::sleep(2);
            $channel->push(['pid1' => Coroutine::getId()]);
        });

        // 创建协程2
        Coroutine::create(function () use ($channel) {
            \Swoole\Coroutine::sleep(2);
            $channel->push(['pid2' => Coroutine::getId()]);
        });

        // 获取协程返回值
        $result = Coroutine::getCoResult($channel, 2);

        $e = microtime(true);

        return 'i am cor:' . ($e - $s) . '|' . json_encode($result);
    }

    public function user()
    {

        if (empty($this->request->getRequestParam('uid'))) {
            throw new \Exception('uid 不能为空');
        }

        $result = UserService::getInstance()->getUserInfoById($this->request->getRequestParam('uid'));
        return json_encode($result);
    }

    public function list()
    {
        $result = UserService::getInstance()->getUserInfoList();
        return json_encode($result);
    }

    public function generate()
    {
        $itemIndex = $this->request->getRequestParam('item');
        $itemIndex || $itemIndex = 1;
        $id = SnowflakeService::getInstance()->generateId($itemIndex);
        return $this->apiSucc($id);
    }

    public function revertId()
    {
        $id = $this->request->getRequestParam('id');
        $info = SnowflakeService::getInstance()->revert($id);

        return $this->apiSucc($info);
    }
}