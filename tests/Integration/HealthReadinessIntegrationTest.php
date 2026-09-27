<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HealthReadinessIntegrationTest extends WebTestCase
{
    public function testReadinessAgainstRealServices(): void
    {
        $client = static::createClient();
        // requires DATABASE_URL / REDIS_URL in .env.test pointing at
        // CI docker-compose services (mysql:3306, redis:6379)

        $client->request('GET', '/readyz');

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testReadinessFailsWhenMySQLUnreachable(): void
    {
        $client = static::createClient();
        $client->getContainer()->set(
            'App\Health\DoctrineMySQLPinger',
            new \App\Tests\Fixtures\FailingPinger()
        );

        $client->request('GET', '/readyz');

        $this->assertSame(503, $client->getResponse()->getStatusCode());
    }
}
