<?php

return [
    'swoole' => [
        'host' => '0.0.0.0',
        'port' => 8077,
        'worker_num' => 4,
        'daemonize' => 1,
        'max_request' => 100000,
    ],
    'mysql' => [
//        'pool_size' => 3,     //连接池大小
//        'pool_get_timeout' => 3, //当在此时间内未获得到一个连接，会立即返回。（表示所以的连接都已在使用中）
//        'master' => [
//            'host' => '192.168.10.10',   //数据库ip
//            'port' => 3306,          //数据库端口
//            'user' => 'homestead',        //数据库用户名
//            'password' => 'secret', //数据库密码
//            'database' => 'homestead',   //默认数据库名
//            'timeout' => 60,       //数据库连接超时时间
//            'charset' => 'utf8mb4', //默认字符集
//            'strict_type' => true,  //ture，会自动表数字转为int类型
//        ],
    ],
    'snowflake' => [
        'node' => 1,
        'item_num' => 64,
    ]
];