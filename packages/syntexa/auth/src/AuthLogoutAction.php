<?php

declare(strict_types=1);

namespace Syntexa\AuthModule;

use Syntexa\Core\Attributes\AsController;
use Syntexa\Core\Response;

/**
 * Single Action Controller - Auth Logout
 */
#[AsController(
    path: '/api/auth/logout',
    methods: ['POST'],
    name: 'auth.logout',
    tags: ['api', 'auth', 'single-action']
)]
class AuthLogoutAction
{
    public function __invoke(): Response
    {
        return Response::json([
            'message' => 'Logout successful',
            'controller_type' => 'single-action'
        ]);
    }
}

