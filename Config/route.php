<?php

return [
    'route' => function (FastRoute\RouteCollector $r) {
        $r->addRoute('GET', '', 'Index@index');
        $r->addRoute('GET', '/index/index', 'Index@index');
        $r->addRoute('GET', '/index/tong', 'Index@tong');
        $r->addRoute('GET', '/index/user', 'Index@user');
    }
];