<?php

return [
    'route' => function (FastRoute\RouteCollector $r) {
        $r->addRoute('GET', '/index/corotine', 'Index@coroutine');

        $r->addRoute('GET', '/index/generate', 'Index@generate');
    }
];