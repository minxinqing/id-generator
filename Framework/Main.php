<?php

namespace Framework;

use Framework\Core\{
    Route,
    Config,
    Log,
    Lock,
    Table
};
use Framework\Coroutine\Context;
use Framework\Coroutine\Coroutine;
use Framework\Pool;

class Main
{

    public static $rootPath;

    public static $frameworkPath;

    public static $applicationPath;

    private static $pidFile;
    private static $swoolePid;

    public static $table;
    public static $lock;

    private static function init()
    {
        Config::load();
        Log::init();
        Route::init();
        Lock::init(16);
        Table::init(16);
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

        self::$pidFile = self::$rootPath . DS . 'runtime' . DS . 'swoole.pid';
        self::$swoolePid = is_file(self::$pidFile) ? file_get_contents(self::$pidFile) : 0;

        $command = $_SERVER['argv'][1];

        switch ($command) {
            case 'start':
                self::start();
                break;
            case 'reload':
                self::reload();
                break;
            case 'restart':
                self::restart();
                break;
            case 'stop':
                self::stop();
                break;
            default:
                echo 'action not defined';
                break;
        }
    }

    private static function start()
    {
        $swooleConfig = Config::get('swoole');
        $http = new \Swoole\Http\Server($swooleConfig['host'], $swooleConfig['port']);
        $http->set($swooleConfig);

        $http->on('start', function (\Swoole\Server $serv) {
            file_put_contents(self::$pidFile, $serv->master_pid);
            self::setProcessTitle('id-generator-master');
        });

        $http->on('managerStart', function (\Swoole\Server $serv) {
            self::setProcessTitle('id-generator-manager');
        });

        $http->on('workerStart', function (\Swoole\Http\Server $serv, int $workerId) {
            if (function_exists('opcache_reset')) {
                \opcache_reset();
            }
            if ($serv->taskworker) {
                self::setProcessTitle('id-generator-task');
            } else {
                self::setProcessTitle('id-generator-worker');
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

        $http->on('shutdown', function () {
            unlink(self::$pidFile);
        });

        $http->start();
    }

    public static function stop()
    {
        if (posix_kill(self::$swoolePid, SIGTERM)) {
            echo "shutdown success\r\n";
        } else {
            echo "shutdown failed\r\n";
        }
    }

    public static function reload()
    {
        if (posix_kill(self::$swoolePid, SIGUSR1)) {
            echo "reload success\r\n";
        } else {
            echo "reload failed\r\n";
        }
    }

    public static function restart()
    {
        self::stop();
        self::start();
    }

    public static function setProcessTitle($title)
    {
        if (function_exists('cli_set_process_title')) {
            @cli_set_process_title($title);
        } elseif (function_exists('\swoole_set_process_name')) {
            \swoole_set_process_name($title);
        }
    }

}
