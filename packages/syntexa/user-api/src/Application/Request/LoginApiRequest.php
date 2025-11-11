<?php

declare(strict_types=1);

namespace Syntexa\User\Application\Request;

use Syntexa\Core\Attributes\AsRequest;
use Syntexa\Core\Contract\RequestInterface;
use Syntexa\User\Application\Response\LoginApiResponse;

#[AsRequest(
    path: '/api/login',
    methods: ['POST'],
    name: 'api.login',
    responseWith: LoginApiResponse::class
)]
class LoginApiRequest implements RequestInterface
{
    public int $id;
}

