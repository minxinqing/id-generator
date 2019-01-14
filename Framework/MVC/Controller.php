<?php

namespace Framework\MVC;

use Framework\Pool\Context as ContextPool;

class Controller
{
    protected $request;

    public function __construct()
    {
        $context = ContextPool::getContext();
        $this->request = $context->getRequest();
    }

    public function apiSucc($data)
    {
        return json_encode([
            'code' => 0,
            'data' => $data,
        ]);
    }

    public function apiError($code, $msg = '', $data = [])
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
        ];

        if ($data) {
            $result['data'] = $data;
        }
        return json_encode($result);
    }
}