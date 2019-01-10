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
}