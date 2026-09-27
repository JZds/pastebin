<?php

namespace App\Health;

interface PingerInterface
{
    /**
     * @throws \Throwable if the dependency is unreachable
     */
    public function ping(int $timeoutSeconds): void;
}
