<?php

declare(strict_types=1);

namespace Syntexa\ApiModule;

use Syntexa\Core\Attributes\AsController;
use Syntexa\Core\Response;

/**
 * Single Action Controller - API Health
 */
#[AsController(
    path: '/api/health',
    methods: ['GET'],
    name: 'api.health',
    tags: ['api', 'composer', 'single-action']
)]
class ApiHealthAction
{
    public function __invoke(): Response
    {
        return Response::json([
            'status' => 'ok',
            'message' => 'API is healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'module' => 'api-module',
            'type' => 'composer',
            'controller_type' => 'single-action'
        ]);
    }
}

