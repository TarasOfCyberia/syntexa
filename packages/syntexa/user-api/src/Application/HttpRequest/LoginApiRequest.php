<?php

declare(strict_types=1);

namespace Syntexa\User\Application\HttpRequest;

use Syntexa\Core\Attributes\AsHttpRequest;
use Syntexa\Core\Contract\RequestInterface;
use Syntexa\User\Application\HttpResponse\LoginApiResponse;

#[AsHttpRequest(
    path: '/api/login',
    methods: ['POST'],
    name: 'api.login',
    responseWith: LoginApiResponse::class
)]
class LoginApiRequest implements RequestInterface
{
}


