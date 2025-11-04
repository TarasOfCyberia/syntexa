<?php

declare(strict_types=1);

namespace Syntexa\AuthModule;

use Syntexa\Core\Attributes\AsController;
use Syntexa\Core\Response;

/**
 * Single Action Controller - Auth Login
 */
#[AsController(
    path: '/api/auth/login',
    methods: ['POST'],
    name: 'auth.login',
    tags: ['api', 'auth', 'single-action']
)]
class AuthLoginAction
{
    public function __invoke(): Response
    {
        return Response::json([
            'message' => 'Login successful',
            'data' => [
                'token' => 'jwt_token_here',
                'user' => [
                    'id' => 1,
                    'name' => 'John Doe',
                    'email' => 'john@example.com'
                ]
            ],
            'controller_type' => 'single-action'
        ]);
    }
}

