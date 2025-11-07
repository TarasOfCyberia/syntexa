<?php

declare(strict_types=1);

namespace Syntexa\User\Application\State\Processor;

use Syntexa\Core\Attributes\AsHttpRequest;
use Syntexa\Core\Contract\RequestInterface;
use Syntexa\User\Application\State\Response\LoginApiStateResponse;

#[AsHttpRequest(
    path: '/api/login',
    methods: ['POST'],
    name: 'api.login',
    responseWith: LoginApiStateResponse::class
)]
class LoginApiStateProcessor implements RequestInterface
{
    public int $id;
}


