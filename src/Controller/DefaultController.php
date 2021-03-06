<?php

declare(strict_types=1);

namespace App\Controller;

use Slim\Http\Request;
use Slim\Http\Response;

final class DefaultController extends BaseController
{
    public const API_VERSION = '1.7.0';

    public function getHelp(Request $request, Response $response): Response
    {
        $app = $this->container->get('settings')['app'];
        $url = $app['domain'];
        $endpoints = [
            'tasks' => $url . '/api/v1/tasks',
            'users' => $url . '/api/v1/users',
            'notes' => $url . '/api/v1/notes',
            'docs' => $url . '/docs/index.html',
            'status' => $url . '/status',
            'this help' => $url . '',
        ];
        $message = [
            'endpoints' => $endpoints,
            'version' => self::API_VERSION,
            'timestamp' => time(),
        ];

        return $this->jsonResponse($response, 'success', $message, 200);
    }

    public function getStatus(Request $request, Response $response): Response
    {
        $status = [
            'stats' => $this->getDbStats(),
            'MySQL' => 'OK',
            'Redis' => $this->checkRedisConnection(),
            'Logger' => $this->checkLoggerConnection(),
            'version' => self::API_VERSION,
            'timestamp' => time(),
        ];

        return $this->jsonResponse($response, 'success', $status, 200);
    }

    private function getDbStats(): array
    {
        $userService = $this->container->get('find_user_service');
        $taskService = $this->container->get('find_task_service');
        $noteService = $this->container->get('find_note_service');

        return [
            'users' => count($userService->getAll()),
            'tasks' => count($taskService->getAll()),
            'notes' => count($noteService->getAll()),
        ];
    }

    private function checkRedisConnection(): string
    {
        $redis = 'Disabled';
        if (self::isRedisEnabled() === true) {
            $redisService = $this->container->get('redis_service');
            $key = $redisService->generateKey('test:status');
            $redisService->set($key, new \stdClass());
            $redis = 'OK';
        }

        return $redis;
    }

    private function checkLoggerConnection(): string
    {
        $logger = 'Disabled';
        if (self::isLoggerEnabled() === true){
            $logger = 'Enabled';
        }

        return $logger;
    }
}
