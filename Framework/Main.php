<?php

namespace Framework;

use Framework\Core\{
    Route,
    Config,
    Log
};
use Framework\Coroutine\Context;
use Framework\Coroutine\Coroutine;
use Framework\Pool;
use Swoole;

class Main
{

    public static $rootPath;

    public static $frameworkPath;

    public static $applicationPath;

    private static function init()
    {
        Config::load();
        Log::init();
        Route::init();
    }

    final public static function run()
    {
        if (!defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }

        self::$rootPath = dirname(__DIR__);
        self::$frameworkPath = self::$rootPath . DS . 'Framework';
        self::$applicationPath = self::$rootPath . DS . 'App';

        self::init();

        $http = new \Swoole\Http\Server(Config::get('host'), Config::get('port'));
        $http->set([
            "worker_num" => Config::get('worker_num'),
            "daemonize" => Config::get('daemonize'),
        ]);

        $http->on('workerStart', function (\Swoole\Http\Server $serv, int $workerId) {
            if (function_exists('opcache_reset')) {
                \opcache_reset();
            }

            try {
                $mysqlConfig = Config::get('mysql');
                if (!empty($mysqlConfig)) {
                    Pool\Mysql::getInstance($mysqlConfig);
                    Log::info('初始化连接池');
                }
            } catch (\Throwable $e) {
                Log::emergency($e->getMessage(), $e->getTrace());
                $serv->shutdown();
            }
        });

        $http->on('request', function ($request, $response) {
            try {
                // 初始化根协程ID
                $coId = Coroutine::setBaseId();
                // 初始化上下文
                $context = new Context($request, $response);
                // 存放容器pool
                Pool\Context::set($context);
                // 协程退出，自动清空
                defer(function () use ($coId) {
                    // 清空当前pool的上下文，释放资源
                    Pool\Context::clear();
                });

                // 自动路由
                $result = Route::dispatch();
                $response->end($result);
            } catch (\Exception $e) {
                Log::alert($e->getMessage(), $e->getTrace());
                $response->end($e->getMessage());
            } catch (\Error $e) {
                Log::emergency($e->getMessage(), $e->getTrace());
                $response->end(500);
            } catch (\Throwable $e) {
                Log::emergency($e->getMessage(), $e->getTrace());
                $response->end(500);
            }

        });
        $http->start();
    }

}
