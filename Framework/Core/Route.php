<?php

namespace Framework\Core;

use Co\Exception;
use FastRoute;
use Framework\Pool\Context;
use Framework\MVC\Controller;


class Route
{
    private static $dispatcher;

    public static function init()
    {
        $routeList = Config::get('route');
        self::$dispatcher = FastRoute\simpleDispatcher($routeList);
    }

    public static function dispatch()
    {
        $request = Context::getContext()->getRequest();
        $path = $request->getUri()->getPath();
        $httpMethod = $request->getMethod();
        if ('/favicon.ico' == $path) {
            return '';
        }

        if (false !== $pos = strpos($path, '?')) {
            $path = substr($path, 0, $pos);
        }
        $path = rawurldecode($path);

        $routeInfo = self::$dispatcher->dispatch($httpMethod, $path);
        $controllerNameSpace = 'App\Controller';
        switch ($routeInfo[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                throw new \Exception('404');
                break;
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                throw new \Exception('405');
                break;
            case FastRoute\Dispatcher::FOUND:
                //匹配的是数组, 格式：['controllerName', 'MethodName']
                if (is_array($routeInfo[1])) {
                    if (!empty($routeInfo[2]) && is_array($routeInfo[2])) {
                        //有默认参数
                        $params = $request->getQueryParams() + $routeInfo[2];
                        $request->withQueryParams($params);
                    }
//                    $request->withAttribute(Controller::_CONTROLLER_KEY_, $routeInfo[1][0]);
//                    $request->withAttribute(Controller::_METHOD_KEY_, $routeInfo[1][1]);
                    $controllerName = $routeInfo[1][0];
                    $methodName = $routeInfo[1][1];
                    $className = $controllerNameSpace.'\\'.$controllerName;
                    $controller = new $className;
                    $result = $controller->$methodName();
                }
                //字符串, 格式：controllerName@MethodName
                elseif (is_string($routeInfo[1])) {
                    if (!empty($routeInfo[2]) && is_array($routeInfo[2])) {
                        //有默认参数
                        $params = $request->getQueryParams() + $routeInfo[2];
                        $request->withQueryParams($params);
                    }
//                    $request->withAttribute(Controller::_CONTROLLER_KEY_, $controllerName);
//                    $request->withAttribute(Controller::_METHOD_KEY_, $methodName);
                    [$controllerName, $methodName] = explode('@', $routeInfo[1]);
                    $className = $controllerNameSpace.'\\'.$controllerName;
                    $controller = new $className();
                    $result = $controller->$methodName();
                }
                //回调函数，直接执行
                elseif (is_callable($routeInfo[1])) {
                    $result = $routeInfo[1](...$routeInfo[2]);
                } else {
                    throw new \Exception('router error');
                }
                break;
        }

        return $result;
    }
}