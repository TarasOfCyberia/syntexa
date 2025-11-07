<?php

declare(strict_types=1);

namespace Syntexa\UserFrontend\Application\State\Provider;

use Syntexa\Core\Attributes\AsHttpRequest;
use Syntexa\Core\Contract\RequestInterface;
use Syntexa\UserFrontend\Application\State\Response\LoginFormStateResponse;

#[AsHttpRequest(path: '/login', name: 'login.form', responseWith: LoginFormStateResponse::class)]
class LoginFormProvider implements RequestInterface
{
}


