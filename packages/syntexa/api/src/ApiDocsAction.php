<?php

declare(strict_types=1);

namespace Syntexa\ApiModule;

use Syntexa\Core\Attributes\AsController;
use Syntexa\Core\Response;

/**
 * Single Action Controller - API Docs
 */
#[AsController(
    path: '/api/docs',
    methods: ['GET'],
    name: 'api.docs',
    tags: ['api', 'composer', 'single-action']
)]
class ApiDocsAction
{
    public function __invoke(): Response
    {
        return Response::json([
            'title' => 'Syntexa API Documentation',
            'version' => '1.0.0',
            'endpoints' => [
                'GET /api/health' => 'Health check',
                'GET /api/version' => 'Version information',
                'GET /api/docs' => 'API documentation'
            ],
            'module' => 'api-module',
            'controller_type' => 'single-action'
        ]);
    }
}

