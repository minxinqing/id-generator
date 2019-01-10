<?php

namespace Framework\Coroutine;

use EasySwoole\Http\Request;
use EasySwoole\Http\Response;

class Context
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    private $map = [];

    public function __construct(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
    {
        $this->request = new Request($request);
        $this->response = new Response($response);
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param $key
     * @param $val
     */
    public function set($key, $val)
    {
        $this->map[$key] = $val;
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        return $this->map[$key] ?? null;
    }
}