<?php

namespace Framework\Core;

class Table{

    /**
     * @var \Swoole\Table
     */
    public static $table;

    public static function init($size = 16)
    {
        // 申明swoole_table，用于进程间数据共享
        // 由于需要在server启动前创建，只能在框架层定义了
        $table = new \Swoole\Table($size);
        $table->column('timestamp', \Swoole\Table::TYPE_INT, 8);
        $table->column('sequence', \Swoole\Table::TYPE_INT, 4);
        $table->create();
        self::$table = $table;
    }

}