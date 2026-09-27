<?php

namespace App\Controller;

use App\Health\PingerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthController extends AbstractController
{
    public function __construct(
        private readonly PingerInterface $mysqlPinger,
        private readonly PingerInterface $redisPinger,
        private readonly int $timeoutSeconds = 2,
    ) {
    }

    #[Route('/healthz', name: 'health_liveness', methods: ['GET'])]
    public function liveness(): Response
    {
        return new Response('OK', Response::HTTP_OK);
    }

    #[Route('/readyz', name: 'health_readiness', methods: ['GET'])]
    public function readiness(): JsonResponse
    {
        $status = ['mysql' => 'ok', 'redis' => 'ok'];
        $healthy = true;

        try {
            $this->mysqlPinger->ping($this->timeoutSeconds);
        } catch (\Throwable $e) {
            $status['mysql'] = 'unhealthy: ' . $e->getMessage();
            $healthy = false;
        }

        try {
            $this->redisPinger->ping($this->timeoutSeconds);
        } catch (\Throwable $e) {
            $status['redis'] = 'unhealthy: ' . $e->getMessage();
            $healthy = false;
        }

        return new JsonResponse(
            $status,
            $healthy ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE
        );
    }
}
