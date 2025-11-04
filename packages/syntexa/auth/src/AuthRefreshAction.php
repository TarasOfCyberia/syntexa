<?php

declare(strict_types=1);

namespace Syntexa\AuthModule;

use Syntexa\Core\Attributes\AsController;
use Syntexa\Core\Response;

/**
 * Single Action Controller - Auth Refresh
 */
#[AsController(
    path: '/api/auth/refresh',
    methods: ['POST'],
    name: 'auth.refresh',
    tags: ['api', 'auth', 'single-action']
)]
class AuthRefreshAction
{
    public function __invoke(): Response
    {
        return Response::json([
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => 'new_jwt_token_here'
            ],
            'controller_type' => 'single-action'
        ]);
    }
}

