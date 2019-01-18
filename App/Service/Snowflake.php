<?php

namespace App\Service;

use Framework\Core\Lock;
use Framework\Core\Table;
use Framework\Core\Singleton;
use Framework\Core\Log;
use Framework\Core\Config;
use Swoole\Exception;

class Snowflake
{
    use Singleton;
    const EPOCH = 1454052772000;// 起始时间戳，毫秒

    const SEQUENCE_BITS = 12;   //序号部分12位
    const SEQUENCE_MAX = -1 ^ (-1 << self::SEQUENCE_BITS);  // 序号最大值

    const NODE_BITS = 4; // 节点编号4位
    const NODE_MAX = -1 ^ (-1 << self::NODE_BITS); // 节点编号最大值

    const ITEM_BITS = 6; // 项目编号6位
    const ITEM_MAX = -1 ^ (-1 << self::ITEM_BITS); // 项目编号最大值

    const ITEM_SHIFT = self::SEQUENCE_BITS;   // 项目编号部分左偏移量 12
    const NODE_SHIFT = self::ITEM_SHIFT + self::ITEM_BITS;// 节点编号部分左偏移量 18
    const TIME_SHIFT = self::NODE_SHIFT + self::NODE_BITS;// 时间戳部分左偏移量 22

    protected $timestamp;   // 上次ID生成时间戳
    protected $sequence;    // 序号

    protected $nodeIndex;

    public function __construct()
    {
        $this->nodeIndex = Config::get('snowflake')['node'];
        if ($this->nodeIndex < 0 || $this->nodeIndex > self::NODE_MAX) {
            throw new Exception('节点编号超出取值范围');
        }
    }

    public function lock($itemIndex)
    {
        Lock::get($itemIndex)->lock();
    }

    public function unlock($itemIndex)
    {
        Lock::get($itemIndex)->unlock();
    }

    public function initAttr($itemIndex)
    {
        $row = Table::$table->get($itemIndex);
        if ($row) {
            $this->timestamp = $row['timestamp'];
            $this->sequence = $row['sequence'];
        } else {
            $this->timestamp = $this->now();
            $this->sequence = 0;
        }
    }

    public function setRow($itemIndex)
    {
        $row = [
            'timestamp' => $this->timestamp,
            'sequence' => $this->sequence,
        ];
        Table::$table->set($itemIndex, $row);
    }

    /**
     * 生成ID
     * @return int
     */
    public function generateId($itemIndex)
    {
        if ($itemIndex < 0 || $itemIndex > self::ITEM_MAX) {
            throw new Exception('项目编号超出取值范围');
        }
        $this->lock($itemIndex);
        $this->initAttr($itemIndex);

        $now = $this->now();
        if ($this->timestamp == $now) {
            $this->sequence++;

            if ($this->sequence > self::SEQUENCE_MAX) {
                // 当前时间生成的序号已经超出最大范围，等待下一时间重新生成
                while ($now <= $this->timestamp) {
                    $now = $this->now();
                }
            }
        } else {
            $this->sequence = 0;
        }

        $this->timestamp = $now;

        $this->setRow($itemIndex);
        $this->unlock($itemIndex);

        $id = (($now - self::EPOCH) << self::TIME_SHIFT) | ($this->nodeIndex << self::NODE_SHIFT) | ($itemIndex << self::ITEM_SHIFT) | $this->sequence;
        return $id;
    }

    /**
     * 获取当前毫秒
     * @return string
     */
    public function now()
    {
        return sprintf("%.0f", microtime(true) * 1000);

    }
}