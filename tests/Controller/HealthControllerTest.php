<?php

namespace App\Tests\Controller;

use App\Controller\HealthController;
use App\Health\PingerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class HealthControllerTest extends TestCase
{
    public function testReadinessReturns200WhenBothHealthy(): void
    {
        $mysql = $this->createMock(PingerInterface::class);
        $redis = $this->createMock(PingerInterface::class);

        $controller = new HealthController($mysql, $redis);
        $response = $controller->readiness();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(
            ['mysql' => 'ok', 'redis' => 'ok'],
            json_decode($response->getContent(), true)
        );
    }

    public function testReadinessReturns503WhenMySQLDown(): void
    {
        $mysql = $this->createMock(PingerInterface::class);
        $mysql->method('ping')->willThrowException(new \RuntimeException('connection refused'));
        $redis = $this->createMock(PingerInterface::class);

        $controller = new HealthController($mysql, $redis);
        $response = $controller->readiness();

        $this->assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertStringContainsString('unhealthy', $body['mysql']);
        $this->assertSame('ok', $body['redis']);
    }

    public function testLivenessNeverDependsOnDependencies(): void
    {
        $mysql = $this->createMock(PingerInterface::class);
        $mysql->expects($this->never())->method('ping');
        $redis = $this->createMock(PingerInterface::class);
        $redis->expects($this->never())->method('ping');

        $controller = new HealthController($mysql, $redis);
        $response = $controller->liveness();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }
}
