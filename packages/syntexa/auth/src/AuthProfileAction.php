<?php

declare(strict_types=1);

namespace Syntexa\AuthModule;

use Syntexa\Core\Attributes\AsController;
use Syntexa\Core\Response;

/**
 * Single Action Controller - Auth Profile
 */
#[AsController(
    path: '/api/auth/me',
    methods: ['GET'],
    name: 'auth.profile',
    tags: ['api', 'auth', 'single-action']
)]
class AuthProfileAction
{
    public function __invoke(): Response
    {
        return Response::json([
            'message' => 'Current user profile',
            'data' => [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'role' => 'admin'
            ],
            'controller_type' => 'single-action'
        ]);
    }
}

