<?php

namespace App\Controller;

use App\Dao\User;
use Framework\Pool\Context;
use App\Service\User as UserService;
use Framework\MVC\Controller;
use Framework\Core\Log;

class Index extends Controller
{
    public function index()
    {
        $request = Context::getContext()->getRequest();
        return 'i am index:' . json_encode($request->get);
    }

    public function tong()
    {
        \Swoole\Runtime::enableCoroutine(true);

        return 'i am tong'.json_encode($this->request->getRequestParam());
    }

    public function user()
    {

        if (empty($this->request->get['uid'])) {
            throw new \Exception('uid 不能为空');
        }

        $result = UserService::getInstance()->getUserInfoById($this->request->get['uid']);
        return json_encode($result);
    }

    public function list()
    {
        $result = UserService::getInstance()->getUserInfoList();
        return json_encode($result);
    }

}