<?php

namespace App\Health;

use Doctrine\DBAL\Connection;

class DoctrineMySQLPinger implements PingerInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function ping(int $timeoutSeconds): void
    {
        // Doctrine DBAL doesn't support per-call timeouts natively;
        // set wrapperClass connection timeout at the DBAL config level instead.
        $this->connection->executeQuery('SELECT 1');
    }
}
