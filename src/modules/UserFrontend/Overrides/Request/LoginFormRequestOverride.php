<?php

declare(strict_types=1);

namespace Syntexa\Modules\UserFrontend\Overrides\Request;

use Syntexa\Core\Attributes\AsRequestOverride;
use Syntexa\UserFrontend\Application\Request\LoginFormRequest;

#[AsRequestOverride(
    of: LoginFormRequest::class,
    path: '/auth',
    methods: ['GET'],
    name: 'login.form',
    priority: 100
)]
class LoginFormRequestOverride
{
    // no body needed; attribute drives the override
}
