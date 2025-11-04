<?php

declare(strict_types=1);

namespace Syntexa\ApiModule;

use Syntexa\Core\Attributes\AsController;
use Syntexa\Core\Response;

/**
 * Single Action Controller - API Version
 */
#[AsController(
    path: '/api/version',
    methods: ['GET'],
    name: 'api.version',
    tags: ['api', 'composer', 'single-action']
)]
class ApiVersionAction
{
    public function __invoke(): Response
    {
        return Response::json([
            'version' => '1.0.0',
            'framework' => 'Syntexa',
            'php_version' => PHP_VERSION,
            'module' => 'api-module',
            'controller_type' => 'single-action'
        ]);
    }
}

