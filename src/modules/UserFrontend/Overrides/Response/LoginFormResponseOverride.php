<?php

declare(strict_types=1);

namespace Syntexa\Modules\UserFrontend\Overrides\Response;

use Syntexa\Core\Attributes\AsResponseOverride;
use Syntexa\Core\Http\Response\ResponseFormat;
use Syntexa\UserFrontend\Application\Response\LoginFormResponse;

#[AsResponseOverride(
    of: LoginFormResponse::class,
    handle: 'auth.login',
    format: ResponseFormat::Layout,
    context: ['title' => 'Sign in'],
    priority: 100
)]
class LoginFormResponseOverride
{
}


