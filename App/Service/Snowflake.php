<?php

namespace App\Service;

use Framework\Core\Lock;
use Framework\Core\Table;
use Framework\Core\Singleton;
use Framework\Core\Log;
use Framework\Core\Config;

class Snowflake
{
    use Singleton;

    const SEQUENCE_MAX = 999;  // 序号最大值

    protected $timestamp;   // 上次ID生成时间戳
    protected $sequence;    // 序号


    public function lock()
    {
        Lock::get(0)->lock();
    }

    public function unlock()
    {
        Lock::get(0)->unlock();
    }

    public function initAttr()
    {
        $row = Table::$table->get('flake');
        if ($row) {
            $this->timestamp = $row['timestamp'];
            $this->sequence = $row['sequence'];
        } else {
            Log::info('重新填充table');
            $this->timestamp = $this->now();
            $this->sequence = Config::get('snowflake')['index'];
        }
    }

    public function setRow()
    {
        $row = [
            'timestamp' => $this->timestamp,
            'sequence' => $this->sequence,
        ];
        Table::$table->set('flake', $row);
    }

    /**
     * 生成ID
     * @return int
     */
    public function generateId()
    {
        $this->lock();
        $this->initAttr();

        $now = $this->now();
        if ($this->timestamp == $now) {
            $this->sequence += Config::get('snowflake')['offset'];

            if ($this->sequence > self::SEQUENCE_MAX) {
                // 当前时间生成的序号已经超出最大范围，等待下一时间重新生成
                while ($now <= $this->timestamp) {
                    $now = $this->now();
                }
            }
        } else {
            $this->sequence = Config::get('snowflake')['index'];
        }

        $this->timestamp = $now;

        $this->setRow();
        $this->unlock();

        $id = $this->timestamp . sprintf('%03d', $this->sequence);
        return $id;
    }

    /**
     * 获取当前毫秒
     * @return string
     */
    public function now()
    {
        $arr = explode(' ', microtime());
        $v = sprintf("%03.0f", $arr[0] * 1000);
        return date('ymdhis', $arr[1]) . $v;
    }
}