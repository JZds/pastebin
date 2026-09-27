<?php

namespace App\Health;

use Redis;

class RedisPinger implements PingerInterface
{
    public function __construct(private readonly Redis $redis)
    {
    }

    public function ping(int $timeoutSeconds): void
    {
        if ($this->redis->ping() !== true && $this->redis->ping() !== '+PONG') {
            throw new \RuntimeException('Redis ping failed');
        }
    }
}
